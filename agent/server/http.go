package server

import (
	"embed"
	"encoding/json"
	"fmt"
	"io/fs"
	"net/http"
	"os/exec"
	"runtime"

	"aopod-agent/autostart"
	"aopod-agent/config"
	"aopod-agent/database"
	"aopod-agent/scheduler"
	"aopod-agent/sender"
	"github.com/kardianos/service"
)

// Server handles the local GUI Web server.
type Server struct {
	WebFS   embed.FS
	Service service.Service
}

func (s *Server) Start(port int) error {
	mux := http.NewServeMux()

	// 1. Static Web GUI files
	subFS, err := fs.Sub(s.WebFS, "web")
	if err == nil {
		fileServer := http.FileServer(http.FS(subFS))
		mux.Handle("/", fileServer)
	}

	// 2. API endpoints for GUI
	mux.HandleFunc("/api/status", s.handleStatus)
	mux.HandleFunc("/api/config", s.handleConfig)
	mux.HandleFunc("/api/test-db", s.handleTestDb)
	mux.HandleFunc("/api/test-api", s.handleTestApi)
	mux.HandleFunc("/api/sync", s.handleSync)
	mux.HandleFunc("/api/logs", s.handleLogs)
	mux.HandleFunc("/api/logs/clear", s.handleClearLogs)
	mux.HandleFunc("/api/open-logs-folder", func(w http.ResponseWriter, r *http.Request) {
		logsDir := scheduler.GetLogsDir()
		_ = exec.Command("explorer.exe", logsDir).Start()
		writeJSON(w, http.StatusOK, map[string]string{"status": "success", "path": logsDir})
	})
	mux.HandleFunc("/api/service/", s.handleService)
	mux.HandleFunc("/api/autostart", func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			var body struct {
				Enabled bool `json:"enabled"`
			}
			_ = json.NewDecoder(r.Body).Decode(&body)
			var err error
			if body.Enabled {
				err = autostart.Enable()
			} else {
				err = autostart.Disable()
			}
			if err != nil {
				writeJSON(w, http.StatusInternalServerError, map[string]string{"error": err.Error()})
				return
			}
		}
		writeJSON(w, http.StatusOK, map[string]interface{}{
			"enabled": autostart.IsEnabled(),
		})
	})
	mux.HandleFunc("/api/open-config-folder", func(w http.ResponseWriter, r *http.Request) {
		_ = exec.Command("explorer.exe", config.GetConfigDir()).Start()
		writeJSON(w, http.StatusOK, map[string]string{"status": "success", "path": config.GetConfigDir()})
	})

	addr := fmt.Sprintf("0.0.0.0:%d", port)
	scheduler.AddLog("INFO", fmt.Sprintf("AOPOD Agent Web GUI เปิดให้บริการที่ http://localhost:%d", port))

	return http.ListenAndServe(addr, mux)
}

func (s *Server) handleStatus(w http.ResponseWriter, r *http.Request) {
	cfg := config.Get()

	dbStatus := "connected"
	if _, err := database.GetDB(); err != nil {
		dbStatus = "disconnected"
	}

	serverStatus := "connected"
	if cfg.Hospital.Token == "" || cfg.Hospital.ServerURL == "" {
		serverStatus = "not_configured"
	}

	serviceRunning := false
	if s.Service != nil {
		if status, err := s.Service.Status(); err == nil {
			serviceRunning = (status == service.StatusRunning)
		}
	}

	lastSync := "-"
	if summary := scheduler.GetLastSyncSummary(); summary != nil {
		lastSync = summary.StartTime.Format("2006-01-02 15:04:05")
	}

	writeJSON(w, http.StatusOK, map[string]interface{}{
		"hospital_code":   cfg.Hospital.Code,
		"hospital_name":   cfg.Hospital.Name,
		"version":         config.AppVersion,
		"db_status":       dbStatus,
		"server_status":     serverStatus,
		"service_running":   serviceRunning,
		"autostart_enabled": autostart.IsEnabled(),
		"last_sync":         lastSync,
		"config_dir":      config.GetConfigDir(),
		"config_path":     config.GetConfigPath(),
	})
}

func (s *Server) handleConfig(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		writeJSON(w, http.StatusOK, config.Get())
		return
	}

	if r.Method == http.MethodPost {
		newCfg := *config.Get()
		if err := json.NewDecoder(r.Body).Decode(&newCfg); err != nil {
			writeJSON(w, http.StatusBadRequest, map[string]string{"error": err.Error()})
			return
		}

		if err := config.Update(&newCfg); err != nil {
			writeJSON(w, http.StatusInternalServerError, map[string]string{"error": err.Error()})
			return
		}

		database.CloseDB()
		scheduler.AddLog("INFO", "อัปเดตการตั้งค่า config.yaml เรียบร้อยแล้ว")
		scheduler.StartCronScheduler() // Restart scheduler with new crons

		writeJSON(w, http.StatusOK, map[string]string{"status": "success", "message": "บันทึกการตั้งค่าเรียบร้อยแล้ว"})
		return
	}

	http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
}

func (s *Server) handleTestDb(w http.ResponseWriter, r *http.Request) {
	dbCfg := config.Get().Database

	if r.Body != nil {
		var req struct {
			Host     *string `json:"host"`
			Port     *int    `json:"port"`
			Username *string `json:"username"`
			Password *string `json:"password"`
			Database *string `json:"database"`
		}
		if err := json.NewDecoder(r.Body).Decode(&req); err == nil {
			if req.Host != nil && *req.Host != "" {
				dbCfg.Host = *req.Host
			}
			if req.Port != nil && *req.Port > 0 {
				dbCfg.Port = *req.Port
			}
			if req.Username != nil && *req.Username != "" {
				dbCfg.Username = *req.Username
			}
			if req.Password != nil {
				dbCfg.Password = *req.Password
			}
			if req.Database != nil && *req.Database != "" {
				dbCfg.Database = *req.Database
			}
		}
	}

	res, err := database.TestConnection(dbCfg)
	if err != nil {
		writeJSON(w, http.StatusOK, map[string]interface{}{
			"status": "error",
			"error":  err.Error(),
		})
		return
	}
	writeJSON(w, http.StatusOK, res)
}

func (s *Server) handleTestApi(w http.ResponseWriter, r *http.Request) {
	cfg := config.Get()
	serverURL := cfg.Hospital.ServerURL
	token := cfg.Hospital.Token
	hospcode := cfg.Hospital.Code

	if r.Body != nil {
		var req struct {
			ServerURL string `json:"server_url"`
			Token     string `json:"token"`
			Hospcode  string `json:"hospcode"`
		}
		if err := json.NewDecoder(r.Body).Decode(&req); err == nil {
			if req.ServerURL != "" {
				serverURL = req.ServerURL
			}
			if req.Token != "" {
				token = req.Token
			}
			if req.Hospcode != "" {
				hospcode = req.Hospcode
			}
		}
	}

	res, err := sender.TestApiConnection(serverURL, token, hospcode)
	if err != nil {
		writeJSON(w, http.StatusOK, map[string]interface{}{
			"status":  "error",
			"message": err.Error(),
		})
		return
	}
	go scheduler.SendHeartbeat(nil)
	writeJSON(w, http.StatusOK, res)
}

func (s *Server) handleSync(w http.ResponseWriter, r *http.Request) {
	var req struct {
		StartDate string   `json:"start_date"`
		EndDate   string   `json:"end_date"`
		Modules   []string `json:"modules"`
	}
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "Invalid JSON body"})
		return
	}

	if req.StartDate == "" || req.EndDate == "" {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "กรุณาระบุ start_date และ end_date"})
		return
	}

	summary, err := scheduler.PerformSyncSelective(req.StartDate, req.EndDate, req.Modules)
	if err != nil {
		writeJSON(w, http.StatusOK, summary)
		return
	}
	writeJSON(w, http.StatusOK, summary)
}

func (s *Server) handleLogs(w http.ResponseWriter, r *http.Request) {
	writeJSON(w, http.StatusOK, scheduler.GetRecentLogs())
}

func (s *Server) handleClearLogs(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		writeJSON(w, http.StatusMethodNotAllowed, map[string]string{"error": "Method not allowed"})
		return
	}
	_ = scheduler.ClearLogs()
	writeJSON(w, http.StatusOK, map[string]string{
		"status":  "success",
		"message": "ล้างประวัติ Log และลบไฟล์ Log เก่าเรียบร้อยแล้ว",
	})
}

func (s *Server) handleService(w http.ResponseWriter, r *http.Request) {
	if s.Service == nil {
		writeJSON(w, http.StatusBadRequest, map[string]string{"error": "Windows service is not supported in this mode"})
		return
	}

	action := r.URL.Path[len("/api/service/"):]
	err := service.Control(s.Service, action)
	if err != nil {
		writeJSON(w, http.StatusOK, map[string]string{"status": "error", "message": err.Error()})
		return
	}

	writeJSON(w, http.StatusOK, map[string]string{"status": "success", "message": fmt.Sprintf("ดำเนินการ %s Service สำเร็จ", action)})
}

func writeJSON(w http.ResponseWriter, status int, data interface{}) {
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(data)
}

// OpenBrowser opens default system web browser to given URL.
func OpenBrowser(url string) {
	switch runtime.GOOS {
	case "windows":
		_ = exec.Command("rundll32", "url.dll,FileProtocolHandler", url).Start()
	case "darwin":
		_ = exec.Command("open", url).Start()
	default:
		_ = exec.Command("xdg-open", url).Start()
	}
}
