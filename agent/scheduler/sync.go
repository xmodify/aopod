package scheduler

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strings"
	"sync"
	"time"

	"aopod-agent/collector"
	"aopod-agent/config"
	"aopod-agent/database"
	"aopod-agent/lookups"
	"aopod-agent/sender"
	"aopod-agent/updater"
)

func init() {
	updater.LogFunc = AddLog
}

var (
	syncMutex       sync.Mutex
	lastSyncSummary *sender.SyncSummary
	recentLogs      []LogEntry
	logMutex        sync.RWMutex
)

type LogEntry struct {
	Timestamp string `json:"timestamp"`
	Level     string `json:"level"`
	Message   string `json:"message"`
}

func AddLog(level, msg string) {
	logMutex.Lock()
	defer logMutex.Unlock()

	now := time.Now()
	entry := LogEntry{
		Timestamp: now.Format("2006-01-02 15:04:05"),
		Level:     level,
		Message:   msg,
	}
	recentLogs = append(recentLogs, entry)
	if len(recentLogs) > 200 {
		recentLogs = recentLogs[len(recentLogs)-200:]
	}

	todayFile := GetTodayLogPath()
	if f, err := os.OpenFile(todayFile, os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666); err == nil {
		_, _ = f.WriteString(fmt.Sprintf("[%s] [%s] %s\n", entry.Timestamp, level, msg))
		_ = f.Close()
	}

	log.Printf("[%s] %s\n", level, msg)
}

func GetRecentLogs() []LogEntry {
	logMutex.RLock()
	defer logMutex.RUnlock()
	logs := make([]LogEntry, len(recentLogs))
	copy(logs, recentLogs)
	return logs
}

func GetLastSyncSummary() *sender.SyncSummary {
	return lastSyncSummary
}

// DateRange defines start and end date for a specific module query.
type DateRange struct {
	StartDate string `json:"start_date"`
	EndDate   string `json:"end_date"`
}

// PerformSync runs the complete sync process for all modules across a date range.
func PerformSync(startDate, endDate string) (*sender.SyncSummary, error) {
	return PerformSyncSelective(startDate, endDate, nil)
}

// PerformSyncSelective runs sync for specific modules across a date range.
// If modules is nil or empty, all modules (bed, opd, ipd, refer, operation) are synced.
func PerformSyncSelective(startDate, endDate string, modules []string) (*sender.SyncSummary, error) {
	return PerformSyncSelectiveWithRanges(startDate, endDate, modules, nil)
}

// PerformSyncSelectiveWithRanges runs sync with optional individual date ranges per module.
func PerformSyncSelectiveWithRanges(startDate, endDate string, modules []string, moduleRanges map[string]DateRange) (*sender.SyncSummary, error) {
	syncMutex.Lock()
	defer syncMutex.Unlock()

	cfg := config.Get()
	startTime := time.Now()
	summary := &sender.SyncSummary{
		Success:   true,
		StartTime: startTime,
	}

	shouldSync := func(mod string) bool {
		if len(modules) == 0 {
			return true
		}
		for _, m := range modules {
			if strings.EqualFold(m, mod) {
				return true
			}
		}
		return false
	}

	getDates := func(mod string) (string, string) {
		if moduleRanges != nil {
			if r, ok := moduleRanges[mod]; ok && r.StartDate != "" && r.EndDate != "" {
				return r.StartDate, r.EndDate
			}
		}
		return startDate, endDate
	}

	modDesc := "ทุกหมวด"
	if len(modules) > 0 {
		modDesc = strings.Join(modules, ", ")
	}
	AddLog("INFO", fmt.Sprintf("เริ่มดึงและส่งข้อมูลช่วงวันที่ %s ถึง %s [%s] (รพ. %s)...", startDate, endDate, modDesc, cfg.Hospital.Code))

	// 1. Connect to Database
	db, err := database.GetDB()
	if err != nil {
		summary.Success = false
		summary.Message = "ไม่สามารถเชื่อมต่อฐานข้อมูล HOSxP ได้: " + err.Error()
		AddLog("ERROR", summary.Message)
		return summary, err
	}

	// 2. Try syncing ICD-10 lookups from server
	if cfg.Hospital.ServerURL != "" {
		_ = lookups.SyncFromServer(cfg.Hospital.ServerURL)
	}

	// 3. Bed Snapshot Real-time (No date range required)
	if shouldSync("bed") {
		bedDepRecords, err := collector.CollectIpdBedDepartment(db)
		if err == nil && len(bedDepRecords) > 0 {
			bedDepResult, _ := sender.SendChunks(bedDepRecords, "/api/ipd_bed_dep", nil, "BEDDEP")
			summary.BedDep = bedDepResult
			AddLog("INFO", fmt.Sprintf("ส่งข้อมูลประเภทเตียงแยกแผนก Real-time: %d แผนกสำเร็จ", bedDepResult.TotalSent))
		}
	}

	// 4. OPD
	if shouldSync("opd") {
		sDate, eDate := getDates("opd")
		opdRecords, err := collector.CollectOPD(db, sDate, eDate)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("เกิดข้อผิดพลาดในการ Query OPD (%s ถึง %s): %s", sDate, eDate, err.Error()))
		} else {
			opdResult, _ := sender.SendChunks(opdRecords, "/api/opd", func(r collector.OpdRecord) string { return r.Vstdate }, "OPD")
			summary.OPD = opdResult
			AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยนอก (OPD %s ถึง %s): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", sDate, eDate, len(opdRecords), opdResult.TotalSent, opdResult.TotalFailed))
		}
	}

	// 5. IPD
	if shouldSync("ipd") {
		sDate, eDate := getDates("ipd")
		ipdRecords, err := collector.CollectIPD(db, sDate, eDate)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("เกิดข้อผิดพลาดในการ Query IPD (%s ถึง %s): %s", sDate, eDate, err.Error()))
		} else {
			ipdResult, _ := sender.SendChunks(ipdRecords, "/api/ipd", func(r collector.IpdRecord) string { return r.Dchdate }, "IPD")
			summary.IPD = ipdResult
			AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยใน (IPD %s ถึง %s): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", sDate, eDate, len(ipdRecords), ipdResult.TotalSent, ipdResult.TotalFailed))
		}
	}

	// 6. Refer
	if shouldSync("refer") {
		sDate, eDate := getDates("refer")
		referRecords, err := collector.CollectRefer(db, sDate, eDate)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("เกิดข้อผิดพลาดในการ Query Refer (%s ถึง %s): %s", sDate, eDate, err.Error()))
		} else {
			referResult, _ := sender.SendChunks(referRecords, "/api/refer", func(r collector.ReferRecord) string { return r.Vstdate }, "REFER")
			summary.Refer = referResult
			AddLog("INFO", fmt.Sprintf("ส่งข้อมูลส่งต่อ (Refer %s ถึง %s): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", sDate, eDate, len(referRecords), referResult.TotalSent, referResult.TotalFailed))
		}
	}

	// 7. Operation
	if shouldSync("operation") {
		sDate, eDate := getDates("operation")
		opRecords, err := collector.CollectOperation(db, sDate, eDate)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("เกิดข้อผิดพลาดในการ Query ผ่าตัด (%s ถึง %s): %s", sDate, eDate, err.Error()))
		} else {
			opResult, _ := sender.SendChunks(opRecords, "/api/operation", func(r collector.OperationRecord) string { return r.Vstdate }, "OPERATION")
			summary.Operation = opResult
			AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผ่าตัด (Operation %s ถึง %s): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", sDate, eDate, len(opRecords), opResult.TotalSent, opResult.TotalFailed))
		}
	}

	summary.DurationMs = time.Since(startTime).Milliseconds()
	if summary.OPD.TotalFailed > 0 || summary.IPD.TotalFailed > 0 || summary.Refer.TotalFailed > 0 || summary.Operation.TotalFailed > 0 {
		summary.Success = false
		summary.Message = "ส่งข้อมูลสำเร็จบางส่วน มีบาง Batch ล้มเหลว"
	} else {
		summary.Message = fmt.Sprintf("ส่งข้อมูลเรียบร้อยแล้ว ใช้เวลา %.2f วินาที", float64(summary.DurationMs)/1000.0)
	}

	lastSyncSummary = summary
	AddLog("INFO", summary.Message)

	// Send Heartbeat to server
	go SendHeartbeat(summary)

	return summary, nil
}

// PerformHourlySync runs Category 1: OPD/Refer/Operation for opdDaysBack, and IPD for ipdDaysBack.
func PerformHourlySync(opdDaysBack, ipdDaysBack int) (*sender.SyncSummary, error) {
	syncMutex.Lock()
	defer syncMutex.Unlock()

	cfg := config.Get()
	startTime := time.Now()
	summary := &sender.SyncSummary{
		Success:   true,
		StartTime: startTime,
	}

	if opdDaysBack <= 0 {
		opdDaysBack = 5
	}
	if ipdDaysBack <= 0 {
		ipdDaysBack = 30
	}

	opdStart := time.Now().AddDate(0, 0, -opdDaysBack).Format("2006-01-02")
	ipdStart := time.Now().AddDate(0, 0, -ipdDaysBack).Format("2006-01-02")
	today := time.Now().Format("2006-01-02")

	AddLog("INFO", fmt.Sprintf("[หมวดที่ 1: รายชั่วโมง] เริ่มส่งข้อมูล OPD/Refer/ผ่าตัด ย้อนหลัง %d วัน และ IPD ย้อนหลัง %d วัน...", opdDaysBack, ipdDaysBack))

	// 1. Connect to Database
	db, err := database.GetDB()
	if err != nil {
		summary.Success = false
		summary.Message = "ไม่สามารถเชื่อมต่อฐานข้อมูล HOSxP ได้: " + err.Error()
		AddLog("ERROR", summary.Message)
		return summary, err
	}

	// 2. Try syncing ICD-10 lookups
	if cfg.Hospital.ServerURL != "" {
		_ = lookups.SyncFromServer(cfg.Hospital.ServerURL)
	}

	// 3. OPD (opdDaysBack)
	opdRecords, err := collector.CollectOPD(db, opdStart, today)
	if err != nil {
		AddLog("ERROR", "Query OPD ล้มเหลว: "+err.Error())
	} else {
		opdResult, _ := sender.SendChunks(opdRecords, "/api/opd", func(r collector.OpdRecord) string { return r.Vstdate }, "OPD")
		summary.OPD = opdResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยนอก (OPD): %d วัน สำเร็จ", len(opdRecords)))
	}

	// 4. IPD (ipdDaysBack)
	ipdRecords, err := collector.CollectIPD(db, ipdStart, today)
	if err != nil {
		AddLog("ERROR", "Query IPD ล้มเหลว: "+err.Error())
	} else {
		ipdResult, _ := sender.SendChunks(ipdRecords, "/api/ipd", func(r collector.IpdRecord) string { return r.Dchdate }, "IPD")
		summary.IPD = ipdResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยใน (IPD): %d วัน สำเร็จ", len(ipdRecords)))
	}

	// 5. Refer (opdDaysBack)
	referRecords, err := collector.CollectRefer(db, opdStart, today)
	if err != nil {
		AddLog("ERROR", "Query Refer ล้มเหลว: "+err.Error())
	} else {
		referResult, _ := sender.SendChunks(referRecords, "/api/refer", func(r collector.ReferRecord) string { return r.Vstdate }, "REFER")
		summary.Refer = referResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลส่งต่อ (Refer): %d วัน สำเร็จ", len(referRecords)))
	}

	// 6. Operation (opdDaysBack)
	opRecords, err := collector.CollectOperation(db, opdStart, today)
	if err != nil {
		AddLog("ERROR", "Query ผ่าตัด ล้มเหลว: "+err.Error())
	} else {
		opResult, _ := sender.SendChunks(opRecords, "/api/operation", func(r collector.OperationRecord) string { return r.Vstdate }, "OPERATION")
		summary.Operation = opResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผ่าตัด (Operation): %d วัน สำเร็จ", len(opRecords)))
	}

	summary.DurationMs = time.Since(startTime).Milliseconds()
	summary.Message = fmt.Sprintf("ส่งข้อมูลรอบรายชั่วโมงเสร็จสิ้น ใช้เวลา %.2f วินาที", float64(summary.DurationMs)/1000.0)
	lastSyncSummary = summary
	AddLog("INFO", summary.Message)
	go SendHeartbeat(summary)

	return summary, nil
}

// PerformBedSync runs Category 2: Bed Snapshot.
func PerformBedSync() (*sender.SyncSummary, error) {
	syncMutex.Lock()
	defer syncMutex.Unlock()

	startTime := time.Now()
	summary := &sender.SyncSummary{
		Success:   true,
		StartTime: startTime,
	}

	db, err := database.GetDB()
	if err != nil {
		return summary, err
	}

	bedDepRecords, err := collector.CollectIpdBedDepartment(db)
	if err == nil && len(bedDepRecords) > 0 {
		bedDepResult, _ := sender.SendChunks(bedDepRecords, "/api/ipd_bed_dep", nil, "BEDDEP")
		summary.BedDep = bedDepResult
		AddLog("INFO", fmt.Sprintf("[หมวดที่ 2: รายนาที] ส่งข้อมูลประเภทเตียงแยกแผนก: %d แผนกสำเร็จ", bedDepResult.TotalSent))
	} else if err != nil {
		AddLog("ERROR", "[หมวดที่ 2: รายนาที] ดึงข้อมูลสถานะเตียงล้มเหลว: "+err.Error())
	}

	summary.DurationMs = time.Since(startTime).Milliseconds()
	lastSyncSummary = summary
	go SendHeartbeat(summary)

	return summary, nil
}

// SendHeartbeat sends live status to central AOPOD server.
func SendHeartbeat(lastSummary *sender.SyncSummary) {
	cfg := config.Get()
	if cfg.Hospital.ServerURL == "" || cfg.Hospital.Token == "" {
		return
	}

	url := fmt.Sprintf("%s/api/agent/heartbeat", stringsTrimRight(cfg.Hospital.ServerURL, "/"))
	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	dbStatus := "connected"
	if _, err := database.GetDB(); err != nil {
		dbStatus = "disconnected: " + err.Error()
	}

	lastError := ""
	if lastSummary != nil && !lastSummary.Success {
		lastError = lastSummary.Message
	}

	hostname, _ := os.Hostname()
	payload := map[string]interface{}{
		"hospcode":      cfg.Hospital.Code,
		"version":       config.AppVersion,
		"status":        "running",
		"db_status":     dbStatus,
		"hostname":      hostname,
		"last_sync_opd": time.Now().Format("2006-01-02 15:04:05"),
		"last_sync_ipd": time.Now().Format("2006-01-02 15:04:05"),
		"last_sync_bed": time.Now().Format("2006-01-02 15:04:05"),
		"last_error":    lastError,
	}

	jsonData, _ := json.Marshal(payload)
	req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
	if err != nil {
		return
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)

	resp, err := client.Do(req)
	if err == nil {
		resp.Body.Close()
	}
}

// PollRemoteTasks polls AOPOD server for any remote sync or update commands.
func PollRemoteTasks() {
	cfg := config.Get()
	if cfg.Hospital.ServerURL == "" || cfg.Hospital.Token == "" {
		return
	}

	url := fmt.Sprintf("%s/api/agent/config", stringsTrimRight(cfg.Hospital.ServerURL, "/"))
	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", url, nil)
	if err != nil {
		return
	}
	req.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)

	resp, err := client.Do(req)
	if err != nil {
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		return
	}

	var result struct {
		Queries        map[string]string `json:"queries"`
		QueriesVersion string            `json:"queries_version"`
		Settings       struct {
			Schedule           *RemoteSchedule `json:"schedule"`
			ProvinceHospcodes  []string        `json:"province_hospcodes"`
			LatestAgentVersion string          `json:"latest_agent_version"`
			AgentDownloadURL   string          `json:"agent_download_url"`
		} `json:"settings"`
		PendingTask    *struct {
			TaskID       string               `json:"task_id"`
			Action       string               `json:"action"`
			StartDate    string               `json:"start_date"`
			EndDate      string               `json:"end_date"`
			Modules      []string             `json:"modules"`
			ModuleRanges map[string]DateRange `json:"module_ranges"`
			DownloadURL  string               `json:"download_url"`
			Version      string               `json:"version"`
		} `json:"pending_task"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err == nil {
		// Update province hospcodes if provided
		if len(result.Settings.ProvinceHospcodes) > 0 {
			collector.SetProvinceHospcodes(result.Settings.ProvinceHospcodes)
		}

		// Apply dynamic remote schedule if provided from central server
		if result.Settings.Schedule != nil {
			ApplyRemoteSchedule(result.Settings.Schedule)
		}

		// Hot-reload dynamic queries if new version received
		if len(result.Queries) > 0 && result.QueriesVersion != "" {
			if collector.UpdateRemoteQueries(result.Queries, result.QueriesVersion) {
				AddLog("INFO", fmt.Sprintf("อัปเดตคำสั่ง SQL ส่วนกลางสำเร็จ (Hot-Reload เวอร์ชัน %s)", result.QueriesVersion))
			}
		}

		// Auto-Check newer agent binary version (Pull update)
		if result.Settings.LatestAgentVersion != "" && !updater.IsUpdating() {
			if updater.IsNewerVersion(result.Settings.LatestAgentVersion, config.AppVersion) {
				AddLog("INFO", fmt.Sprintf("[Auto-Update] 🚀 ตรวจพบ Agent เวอร์ชั่นใหม่ v%s (ปัจจุบัน v%s) กำลังดาวน์โหลดและอัปเดตอัตโนมัติ...", result.Settings.LatestAgentVersion, config.AppVersion))
				downloadURL := result.Settings.AgentDownloadURL
				go func() {
					_ = updater.PerformSelfUpdate(downloadURL, result.Settings.LatestAgentVersion, "auto_version_check")
				}()
			}
		}

		if result.PendingTask != nil {
			task := result.PendingTask

			switch task.Action {
			case "update_client":
				if !updater.IsUpdating() {
					go func() {
						_ = updater.PerformSelfUpdate(task.DownloadURL, task.Version, task.TaskID)
					}()
				}

			case "sync_range":
				fallthrough
			default:
				modDesc := "ทุกหมวด"
				if len(task.Modules) > 0 {
					modDesc = strings.Join(task.Modules, ", ")
				}
				AddLog("INFO", fmt.Sprintf("ได้รับคำสั่งรีโมทจากส่วนกลาง: Task %s (%s ถึง %s) [หมวด: %s]...", task.TaskID, task.StartDate, task.EndDate, modDesc))
				go func() {
					_, _ = PerformSyncSelectiveWithRanges(task.StartDate, task.EndDate, task.Modules, task.ModuleRanges)
					// Acknowledge task completion
					ackURL := fmt.Sprintf("%s/api/agent/task/complete", stringsTrimRight(cfg.Hospital.ServerURL, "/"))
					ackData, _ := json.Marshal(map[string]string{"task_id": task.TaskID, "hospcode": cfg.Hospital.Code})
					ackReq, _ := http.NewRequest("POST", ackURL, bytes.NewBuffer(ackData))
					ackReq.Header.Set("Content-Type", "application/json")
					ackReq.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)
					if ackResp, err := client.Do(ackReq); err == nil {
						ackResp.Body.Close()
					}
				}()
			}
		}
	}
}

func stringsTrimRight(s, cutset string) string {
	for len(s) > 0 && s[len(s)-1] == cutset[0] {
		s = s[:len(s)-1]
	}
	return s
}
