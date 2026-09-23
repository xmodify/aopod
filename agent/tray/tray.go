package tray

import (
	_ "embed"
	"fmt"
	"os"
	"os/exec"
	"time"

	"aopod-agent/config"
	"aopod-agent/scheduler"
	"aopod-agent/server"
	"github.com/getlantern/systray"
)

//go:embed icon.ico
var iconData []byte

var (
	onExitCallback func()
)

// Run starts the Windows system tray. This must be called from the main thread and is blocking.
func Run(onExit func()) {
	onExitCallback = onExit
	systray.Run(onReady, onSystrayExit)
}

func onReady() {
	cfg := config.Get()
	guiURL := fmt.Sprintf("http://localhost:%d", cfg.Web.Port)
	if cfg.Web.Port <= 0 {
		guiURL = "http://localhost:8989"
	}

	systray.SetIcon(iconData)
	systray.SetTitle("AOPOD Agent")
	hospName := cfg.Hospital.Name
	if hospName == "" {
		hospName = "AOPOD Hospital Agent"
	}
	systray.SetTooltip(fmt.Sprintf("%s (%s) - AOPOD Agent", hospName, cfg.Hospital.Code))

	// Menu Items
	mHeader := systray.AddMenuItem(fmt.Sprintf("🏥 %s (%s)", hospName, cfg.Hospital.Code), "ข้อมูลโรงพยาบาล")
	mHeader.Disable()

	systray.AddSeparator()

	mOpen := systray.AddMenuItem("🌐 Open Control Panel", "เปิดหน้าต่าง Web Dashboard บนเบราว์เซอร์")
	mSync := systray.AddMenuItem("⚡ Sync Data Now", "สั่งส่งข้อมูลย้อนหลังทันที")
	mFolder := systray.AddMenuItem("📁 Open Config Folder (%AppData%)", "เปิดโฟลเดอร์เก็บไฟล์ config.yaml ใน Roaming AppData")

	systray.AddSeparator()

	mQuit := systray.AddMenuItem("❌ Exit AOPOD Agent", "ปิดการทำงานของโปรแกรม AOPOD Agent")

	// Event handling loop
	go func() {
		for {
			select {
			case <-mOpen.ClickedCh:
				server.OpenBrowser(guiURL)

			case <-mSync.ClickedCh:
				go func() {
					daysBack := cfg.Schedule.SyncDaysBack
					if daysBack <= 0 {
						daysBack = 10
					}
					startDate := time.Now().AddDate(0, 0, -daysBack).Format("2006-01-02")
					endDate := time.Now().Format("2006-01-02")
					scheduler.AddLog("INFO", fmt.Sprintf("[Tray] สั่งส่งข้อมูลย้อนหลัง %s ถึง %s", startDate, endDate))
					summary, err := scheduler.PerformSync(startDate, endDate)
					if err != nil {
						scheduler.AddLog("ERROR", fmt.Sprintf("[Tray] ส่งข้อมูลล้มเหลว: %v", err))
					} else {
						scheduler.AddLog("SUCCESS", fmt.Sprintf("[Tray] %s (เตียง: สำเร็จ, IPD: %d, OPD: %d, Refer: %d, ผ่าตัด: %d)", summary.Message, summary.IPD.TotalSent, summary.OPD.TotalSent, summary.Refer.TotalSent, summary.Operation.TotalSent))
					}
				}()

			case <-mFolder.ClickedCh:
				_ = exec.Command("explorer.exe", config.GetConfigDir()).Start()

			case <-mQuit.ClickedCh:
				systray.Quit()
				if onExitCallback != nil {
					onExitCallback()
				}
				os.Exit(0)
			}
		}
	}()
}

func onSystrayExit() {
	if onExitCallback != nil {
		onExitCallback()
	}
}

// Quit terminates the system tray.
func Quit() {
	systray.Quit()
}
