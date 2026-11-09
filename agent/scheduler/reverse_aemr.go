package scheduler

import (
	"bytes"
	"crypto/tls"
	"encoding/json"
	"fmt"
	"net/http"
	"sync"
	"time"

	"aopod-agent/collector"
	"aopod-agent/config"
	"aopod-agent/database"
)

type AemrTask struct {
	TaskID       string  `json:"task_id"`
	Type         string  `json:"type"` // "patient_search", "visit_detail" or "opoh_doctor_records"
	CID          string  `json:"cid"`
	VN           string  `json:"vn"`
	HospitalCode string  `json:"hospital_code"`
	Timestamp    float64 `json:"timestamp"`

	// Params ใช้กับ task "opoh_doctor_records" (ดึงรายเคสของแพทย์ ตามช่วงวันที่ แบ่งหน้า)
	Params *collector.DoctorRecordsParams `json:"params,omitempty"`
}

type PollTaskResponse struct {
	Status string    `json:"status"` // "has_task" or "no_task"
	Task   *AemrTask `json:"task"`
}

var (
	reverseWorkerOnce sync.Once
	isWorkerRunning   bool
	workerMutex       sync.Mutex
)

// StartReverseAemrWorker runs a continuous loop to receive real-time EMR query requests without opening ports.
func StartReverseAemrWorker() {
	workerMutex.Lock()
	if isWorkerRunning {
		workerMutex.Unlock()
		return
	}
	isWorkerRunning = true
	workerMutex.Unlock()

	client := &http.Client{
		Timeout: 25 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	AddLog("INFO", "ระบบ Real-time A-EMR Reverse Worker เริ่มต้นทำงาน (Zero-Port Mode)")

	for {
		cfg := config.Get()
		if cfg.Hospital.ServerURL == "" || cfg.Hospital.Token == "" {
			time.Sleep(3 * time.Second)
			continue
		}

		pollURL := fmt.Sprintf("%s/api/agent/aemr/poll-task", stringsTrimRight(cfg.Hospital.ServerURL, "/"))
		payload := map[string]string{
			"hospcode": cfg.Hospital.Code,
		}
		jsonPayload, _ := json.Marshal(payload)

		req, err := http.NewRequest("POST", pollURL, bytes.NewBuffer(jsonPayload))
		if err != nil {
			time.Sleep(2 * time.Second)
			continue
		}
		req.Header.Set("Content-Type", "application/json")
		req.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)

		resp, err := client.Do(req)
		if err != nil {
			time.Sleep(1 * time.Second)
			continue
		}

		var pollResp PollTaskResponse
		err = json.NewDecoder(resp.Body).Decode(&pollResp)
		resp.Body.Close()

		if err != nil || pollResp.Status != "has_task" || pollResp.Task == nil {
			time.Sleep(150 * time.Millisecond)
			continue
		}

		// Handle task in a goroutine so the listener immediately polls again
		go handleAemrTask(pollResp.Task, cfg)
	}
}

func handleAemrTask(task *AemrTask, cfg *config.Config) {
	db, err := database.GetDB()
	if err != nil {
		submitAemrResult(task.TaskID, false, false, "Database connection error: "+err.Error(), nil, cfg)
		return
	}

	switch task.Type {
	case "patient_search":
		AddLog("INFO", fmt.Sprintf("[A-EMR] ได้รับคำร้องขอสืบค้นประวัติ CID: %s (Task: %s)", task.CID, task.TaskID))
		emr, err := collector.CollectPatientAEMR(db, task.CID)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("[A-EMR] สืบค้นประวัติผิดพลาด: %v", err))
			submitAemrResult(task.TaskID, false, false, err.Error(), nil, cfg)
			return
		}
		if emr == nil {
			AddLog("INFO", fmt.Sprintf("[A-EMR] ไม่พบประวัติผู้ป่วย CID: %s", task.CID))
			submitAemrResult(task.TaskID, true, false, "not_found", nil, cfg)
			return
		}
		AddLog("INFO", fmt.Sprintf("[A-EMR] พบประวัติ HN: %s (%s) ส่งข้อมูลกลับเซิร์ฟเวอร์เรียบร้อย", emr.HN, emr.FullName))
		submitAemrResult(task.TaskID, true, true, "", emr, cfg)

	case "visit_detail":
		AddLog("INFO", fmt.Sprintf("[A-EMR] ได้รับคำร้องขอรายละเอียดการตรวจ VN: %s (Task: %s)", task.VN, task.TaskID))
		detail, err := collector.CollectVisitDetail(db, task.VN)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("[A-EMR] สืบค้นรายละเอียดการตรวจผิดพลาด: %v", err))
			submitAemrResult(task.TaskID, false, false, err.Error(), nil, cfg)
			return
		}
		if detail == nil {
			submitAemrResult(task.TaskID, true, false, "not_found", nil, cfg)
			return
		}
		submitAemrResult(task.TaskID, true, true, "", detail, cfg)

	case "opoh_doctor_records":
		if task.Params == nil {
			submitAemrResult(task.TaskID, false, false, "ไม่มีพารามิเตอร์ของงาน", nil, cfg)
			return
		}
		AddLog("INFO", fmt.Sprintf("[OPOH] ได้รับคำร้องขอรายเคสแพทย์ (%s) %s ถึง %s หน้า offset=%d (Task: %s)",
			task.Params.Type, task.Params.StartDate, task.Params.EndDate, task.Params.Offset, task.TaskID))
		res, err := collector.CollectOpohDoctorRecords(db, *task.Params)
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("[OPOH] ดึงรายเคสแพทย์ผิดพลาด: %v", err))
			submitAemrResult(task.TaskID, false, false, err.Error(), nil, cfg)
			return
		}
		submitAemrResult(task.TaskID, true, true, "", res, cfg)
	}
}

func submitAemrResult(taskID string, success bool, found bool, message string, data interface{}, cfg *config.Config) {
	submitURL := fmt.Sprintf("%s/api/agent/aemr/submit-result", stringsTrimRight(cfg.Hospital.ServerURL, "/"))
	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	payload := map[string]interface{}{
		"hospcode": cfg.Hospital.Code,
		"task_id":  taskID,
		"success":  success,
		"found":    found,
		"message":  message,
		"data":     data,
	}

	jsonPayload, _ := json.Marshal(payload)
	req, err := http.NewRequest("POST", submitURL, bytes.NewBuffer(jsonPayload))
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
