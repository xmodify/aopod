package scheduler

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
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

	entry := LogEntry{
		Timestamp: time.Now().Format("2006-01-02 15:04:05"),
		Level:     level,
		Message:   msg,
	}
	recentLogs = append(recentLogs, entry)
	if len(recentLogs) > 100 {
		recentLogs = recentLogs[len(recentLogs)-100:]
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

// PerformSync runs the complete sync process for a given date range.
func PerformSync(startDate, endDate string) (*sender.SyncSummary, error) {
	syncMutex.Lock()
	defer syncMutex.Unlock()

	cfg := config.Get()
	startTime := time.Now()
	summary := &sender.SyncSummary{
		Success:   true,
		StartTime: startTime,
	}

	AddLog("INFO", fmt.Sprintf("เริ่มดึงและส่งข้อมูลช่วงวันที่ %s ถึง %s (รพ. %s)...", startDate, endDate, cfg.Hospital.Code))

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

	// 3. Collect & Send Hospital Bed Snapshot
	bedRecords, err := collector.CollectHospitalBed(db)
	if err == nil && len(bedRecords) > 0 {
		bedResult, _ := sender.SendChunks(bedRecords, "/api/hospital_config", nil, "HOSPITAL")
		summary.Bed = bedResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลเตียงรวม: %d เตียงสำเร็จ", bedResult.TotalSent))
	}

	// 4. Collect & Send Bed Department Snapshot
	bedDepRecords, err := collector.CollectIpdBedDepartment(db)
	if err == nil && len(bedDepRecords) > 0 {
		bedDepResult, _ := sender.SendChunks(bedDepRecords, "/api/ipd_bed_dep", nil, "BEDDEP")
		summary.BedDep = bedDepResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลประเภทเตียงแยกแผนก: %d แผนกสำเร็จ", bedDepResult.TotalSent))
	}

	// 5. Collect & Send OPD
	opdRecords, err := collector.CollectOPD(db, startDate, endDate)
	if err != nil {
		AddLog("ERROR", "เกิดข้อผิดพลาดในการ Query OPD: "+err.Error())
	} else {
		opdResult, _ := sender.SendChunks(opdRecords, "/api/opd", func(r collector.OpdRecord) string { return r.Vstdate }, "OPD")
		summary.OPD = opdResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยนอก (OPD): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", len(opdRecords), opdResult.TotalSent, opdResult.TotalFailed))
	}

	// 6. Collect & Send IPD
	ipdRecords, err := collector.CollectIPD(db, startDate, endDate)
	if err != nil {
		AddLog("ERROR", "เกิดข้อผิดพลาดในการ Query IPD: "+err.Error())
	} else {
		ipdResult, _ := sender.SendChunks(ipdRecords, "/api/ipd", func(r collector.IpdRecord) string { return r.Dchdate }, "IPD")
		summary.IPD = ipdResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผู้ป่วยใน (IPD): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", len(ipdRecords), ipdResult.TotalSent, ipdResult.TotalFailed))
	}

	// 7. Collect & Send Refer
	referRecords, err := collector.CollectRefer(db, startDate, endDate)
	if err != nil {
		AddLog("ERROR", "เกิดข้อผิดพลาดในการ Query Refer: "+err.Error())
	} else {
		referResult, _ := sender.SendChunks(referRecords, "/api/refer", func(r collector.ReferRecord) string { return r.Vstdate }, "REFER")
		summary.Refer = referResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลส่งต่อ (Refer): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", len(referRecords), referResult.TotalSent, referResult.TotalFailed))
	}

	// 8. Collect & Send Operation
	opRecords, err := collector.CollectOperation(db, startDate, endDate)
	if err != nil {
		AddLog("ERROR", "เกิดข้อผิดพลาดในการ Query ผ่าตัด (Operation): "+err.Error())
	} else {
		opResult, _ := sender.SendChunks(opRecords, "/api/operation", func(r collector.OperationRecord) string { return r.Vstdate }, "OPERATION")
		summary.Operation = opResult
		AddLog("INFO", fmt.Sprintf("ส่งข้อมูลผ่าตัด (Operation): %d วัน (%d รายการ) สำเร็จ (ล้มเหลว: %d)", len(opRecords), opResult.TotalSent, opResult.TotalFailed))
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
			ProvinceHospcodes []string `json:"province_hospcodes"`
		} `json:"settings"`
		PendingTask    *struct {
			TaskID      string `json:"task_id"`
			Action      string `json:"action"`
			StartDate   string `json:"start_date"`
			EndDate     string `json:"end_date"`
			DownloadURL string `json:"download_url"`
			Version     string `json:"version"`
		} `json:"pending_task"`
	}

	if err := json.NewDecoder(resp.Body).Decode(&result); err == nil {
		// Update province hospcodes if provided
		if len(result.Settings.ProvinceHospcodes) > 0 {
			collector.SetProvinceHospcodes(result.Settings.ProvinceHospcodes)
		}

		// Hot-reload dynamic queries if new version received
		if len(result.Queries) > 0 && result.QueriesVersion != "" {
			if collector.UpdateRemoteQueries(result.Queries, result.QueriesVersion) {
				AddLog("INFO", fmt.Sprintf("อัปเดตคำสั่ง SQL ส่วนกลางสำเร็จ (Hot-Reload เวอร์ชัน %s)", result.QueriesVersion))
			}
		}

		if result.PendingTask != nil {
			task := result.PendingTask

			switch task.Action {
			case "update_client":
				go func() {
					_ = updater.PerformSelfUpdate(task.DownloadURL, task.Version, task.TaskID)
				}()

			case "sync_range":
				fallthrough
			default:
				AddLog("INFO", fmt.Sprintf("ได้รับคำสั่งรีโมทจากส่วนกลาง: Task %s (%s ถึง %s)...", task.TaskID, task.StartDate, task.EndDate))
				go func() {
					_, _ = PerformSync(task.StartDate, task.EndDate)
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
