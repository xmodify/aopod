package main

import (
	"embed"
	"flag"
	"fmt"
	"log"
	"os"
	"os/signal"
	"syscall"
	"time"

	"aopod-agent/autostart"
	"aopod-agent/config"
	"aopod-agent/database"
	"aopod-agent/scheduler"
	"aopod-agent/sender"
	"aopod-agent/server"
	"aopod-agent/singleinstance"
	"aopod-agent/tray"
	"aopod-agent/updater"
	"github.com/kardianos/service"
)

//go:embed web/*
var webFS embed.FS

type program struct {
	server *server.Server
}

func (p *program) Start(s service.Service) error {
	go p.run()
	return nil
}

func (p *program) run() {
	cfg, err := config.Load()
	if err != nil {
		log.Printf("Warning loading config: %v\n", err)
	}

	// 1. Start background Cron Scheduler & Polling
	scheduler.StartCronScheduler()

	// 2. Start Embedded Web GUI Server
	port := cfg.Web.Port
	if port <= 0 {
		port = 8989
	}
	if err := p.server.Start(port); err != nil {
		log.Printf("Web server error: %v\n", err)
	}
}

func (p *program) Stop(s service.Service) error {
	scheduler.StopCronScheduler()
	return nil
}

func main() {
	updater.CleanOldBinary()

	scheduler.InitLogger()
	scheduler.CleanOldLogs(30)
	log.Println("Starting AOPOD Agent process...")

	var isServiceMode bool
	var isAutoStart bool
	flag.BoolVar(&isServiceMode, "service", false, "Run as background Windows Service")
	flag.BoolVar(&isAutoStart, "autostart", false, "Run quietly in system tray on Windows boot")
	flag.Parse()

	svcConfig := &service.Config{
		Name:        "AOPODAgent",
		DisplayName: "AOPOD Hospital Sync Agent",
		Description: "AOPOD Automatic Hospital Data Synchronization Agent for HOSxP and AOPOD Server",
		Arguments:   []string{"--service"},
	}

	prg := &program{}
	s, err := service.New(prg, svcConfig)
	if err != nil {
		log.Fatalf("Error creating service: %v\n", err)
	}
	prg.server = &server.Server{
		WebFS:   webFS,
		Service: s,
	}

	args := flag.Args()

	if len(args) > 0 {
		singleinstance.AttachParentConsole()
		cmd := args[0]
		switch cmd {
		case "install", "uninstall", "start", "stop", "restart":
			err := service.Control(s, cmd)
			if err != nil {
				fmt.Printf("❌ Failed to %s service: %v\n", cmd, err)
				os.Exit(1)
			}
			fmt.Printf("✅ Windows Service %s completed successfully.\n", cmd)
			return

		case "test-db":
			cfg, _ := config.Load()
			fmt.Printf("🔍 Testing connection to HOSxP (%s:%d/%s)...\n", cfg.Database.Host, cfg.Database.Port, cfg.Database.Database)
			res, err := database.TestConnection(cfg.Database)
			if err != nil {
				fmt.Printf("❌ Connection failed: %v\n", err)
				os.Exit(1)
			}
			fmt.Printf("✅ Connected successfully!\n- Hospital: %v (%v)\n- Latency: %v ms\n- Total tables: %v\n",
				res["hospital_name"], res["hospital_code"], res["latency_ms"], res["table_count"])
			return

		case "test-api":
			cfg, _ := config.Load()
			fmt.Printf("🔍 Testing connection to AOPOD Server (%s)...\n", cfg.Hospital.ServerURL)
			res, err := sender.TestApiConnection(cfg.Hospital.ServerURL, cfg.Hospital.Token, cfg.Hospital.Code)
			if err != nil {
				fmt.Printf("❌ Server connection failed: %v\n", err)
				os.Exit(1)
			}
			fmt.Printf("✅ %v (HTTP %v, %v ms)\n", res["message"], res["http_code"], res["latency_ms"])
			return

		case "sync-now":
			cfg, _ := config.Load()
			daysBack := cfg.Schedule.SyncDaysBack
			if daysBack <= 0 {
				daysBack = 10
			}
			startDate := time.Now().AddDate(0, 0, -daysBack).Format("2006-01-02")
			endDate := time.Now().Format("2006-01-02")
			fmt.Printf("🚀 Starting manual sync from %s to %s for hospital %s...\n", startDate, endDate, cfg.Hospital.Code)
			summary, err := scheduler.PerformSync(startDate, endDate)
			if err != nil {
				fmt.Printf("❌ Sync error: %v\n", err)
				os.Exit(1)
			}
			fmt.Printf("✅ %s\n- OPD sent: %d\n- IPD sent: %d\n- Refer sent: %d\n- Operation sent: %d\n", summary.Message, summary.OPD.TotalSent, summary.IPD.TotalSent, summary.Refer.TotalSent, summary.Operation.TotalSent)
			return
		}
	}

	// Windows Service Mode (when started by Windows Service Manager with --service)
	if isServiceMode {
		if err := s.Run(); err != nil {
			log.Fatalf("Error running service: %v\n", err)
		}
		return
	}

	// Interactive / Standalone Desktop Mode with System Tray & Single Instance Protection
	// 1. Single Instance Protection (Prevent opening duplicate instances)
	if !singleinstance.Lock("Local\\AOPOD_AGENT_SINGLE_INSTANCE_MUTEX") {
		log.Println("Another instance is already running. Opening browser and exiting duplicate instance.")
		cfg, _ := config.Load()
		port := cfg.Web.Port
		if port <= 0 {
			port = 8989
		}
		guiURL := fmt.Sprintf("http://localhost:%d", port)
		
		// Bring up existing running instance dashboard in browser quietly
		server.OpenBrowser(guiURL)
		return
	}

	cfg, _ := config.Load()
	port := cfg.Web.Port
	if port <= 0 {
		port = 8989
	}
	guiURL := fmt.Sprintf("http://localhost:%d", port)

	log.Printf("Starting AOPOD Agent on port %d...\n", port)

	// 2. Ensure Windows Auto-Start (Starts automatically whenever Windows boots/restarts)
	autostart.EnsureAutoStart()

	// 3. Start Web Server & Cron in background goroutines
	go prg.run()

	// 4. Open browser (only if launched interactively, not when started silently on Windows boot)
	if !isAutoStart {
		go func() {
			time.Sleep(800 * time.Millisecond)
			server.OpenBrowser(guiURL)
		}()
	}

	// 4. Run System Tray on main UI thread (Blocks until user exits from tray)
	log.Println("Starting systray loop...")
	tray.Run(func() {
		log.Println("Systray exiting...")
		_ = prg.Stop(s)
	})

	// 5. Fallback wait in case tray returns (e.g. headless/non-desktop session)
	log.Println("Tray ended, waiting on OS signals...")
	stopChan := make(chan os.Signal, 1)
	signal.Notify(stopChan, os.Interrupt, syscall.SIGTERM)
	<-stopChan
	log.Println("Received termination signal, shutting down...")
	_ = prg.Stop(s)
}
