package sender

import (
	"bytes"
	"crypto/sha256"
	"crypto/tls"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"runtime"
	"sort"
	"time"

	"aopod-agent/config"
)

type BatchResult struct {
	TotalSent   int      `json:"total_sent"`
	TotalFailed int      `json:"total_failed"`
	Batches     int      `json:"batches"`
	Errors      []string `json:"errors"`
}

type SyncSummary struct {
	Success    bool        `json:"success"`
	StartTime  time.Time   `json:"start_time"`
	DurationMs int64       `json:"duration_ms"`
	OPD        BatchResult `json:"opd"`
	IPD        BatchResult `json:"ipd"`
	Bed        BatchResult `json:"bed"`
	BedDep     BatchResult `json:"bed_dep"`
	Message    string      `json:"message"`
}

// GetOptimalThreads returns automatic worker threads count based on CPU cores.
func GetOptimalThreads() int {
	cores := runtime.NumCPU()
	if cores <= 2 {
		return 2
	}
	if cores <= 4 {
		return 3
	}
	return 4 // Capped at 4 to protect hospital MySQL DB from connection exhaustion
}

// GetOptimalChunkSize returns optimal batch size based on machine hardware spec.
func GetOptimalChunkSize() int {
	cores := runtime.NumCPU()
	if cores <= 2 {
		return 150
	}
	return 250
}

// SendChunks sends any slice of records in batches of chunkSize to target URL.
func SendChunks[T any](records []T, endpoint string, dateGetter func(item T) string, prefix string) (BatchResult, error) {
	cfg := config.Get()
	serverURL := stringsTrimRight(cfg.Hospital.ServerURL, "/")
	fullURL := fmt.Sprintf("%s%s", serverURL, endpoint)
	token := cfg.Hospital.Token
	hospcode := cfg.Hospital.Code
	chunkSize := cfg.Schedule.ChunkSize
	if chunkSize <= 0 {
		chunkSize = GetOptimalChunkSize()
	}

	result := BatchResult{
		Errors: []string{},
	}

	if len(records) == 0 {
		return result, nil
	}

	// Split into chunks
	for i := 0; i < len(records); i += chunkSize {
		end := i + chunkSize
		if end > len(records) {
			end = len(records)
		}
		chunk := records[i:end]
		result.Batches++

		// Calculate Idempotency-Key
		var dates []string
		if dateGetter != nil {
			for _, item := range chunk {
				if d := dateGetter(item); d != "" {
					dates = append(dates, d)
				}
			}
			sort.Strings(dates)
		}
		keySource := fmt.Sprintf("%s|%s|%s|%d", hospcode, prefix, stringsJoin(dates, ","), i)
		hash := sha256.Sum256([]byte(keySource))
		idempotencyKey := hex.EncodeToString(hash[:])

		payload := map[string]interface{}{
			"records": chunk,
		}

		err := postWithRetry(fullURL, token, idempotencyKey, payload, 3)
		if err != nil {
			result.TotalFailed += len(chunk)
			result.Errors = append(result.Errors, fmt.Sprintf("Batch %d error: %v", result.Batches, err))
		} else {
			result.TotalSent += len(chunk)
		}
	}

	return result, nil
}

func postWithRetry(url, token, idempotencyKey string, payload interface{}, maxRetries int) error {
	jsonData, err := json.Marshal(payload)
	if err != nil {
		return err
	}

	client := &http.Client{
		Timeout: 30 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	var lastErr error
	for attempt := 1; attempt <= maxRetries; attempt++ {
		req, err := http.NewRequest("POST", url, bytes.NewBuffer(jsonData))
		if err != nil {
			return err
		}

		req.Header.Set("Content-Type", "application/json")
		req.Header.Set("Accept", "application/json")
		req.Header.Set("Idempotency-Key", idempotencyKey)
		if token != "" {
			req.Header.Set("Authorization", "Bearer "+token)
		}

		resp, err := client.Do(req)
		if err != nil {
			lastErr = err
			time.Sleep(time.Duration(attempt*500) * time.Millisecond)
			continue
		}

		body, _ := io.ReadAll(resp.Body)
		resp.Body.Close()

		if resp.StatusCode == http.StatusOK || resp.StatusCode == http.StatusMultiStatus || resp.StatusCode == http.StatusCreated {
			return nil
		}

		lastErr = fmt.Errorf("HTTP %d: %s", resp.StatusCode, string(body))
		if resp.StatusCode == 401 || resp.StatusCode == 403 || resp.StatusCode == 422 {
			// Do not retry authorization or validation errors
			return lastErr
		}

		time.Sleep(time.Duration(attempt*500) * time.Millisecond)
	}

	return lastErr
}

// TestApiConnection tests connection and Token validity with server.
func TestApiConnection(serverURL, token, hospcode string) (map[string]interface{}, error) {
	cleanURL := stringsTrimRight(serverURL, "/") + "/api/agent/verify"

	client := &http.Client{
		Timeout: 10 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	payload := map[string]interface{}{
		"hospcode": hospcode,
	}
	jsonData, _ := json.Marshal(payload)

	req, err := http.NewRequest("POST", cleanURL, bytes.NewBuffer(jsonData))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Accept", "application/json")
	if token != "" {
		req.Header.Set("Authorization", "Bearer "+token)
	}

	start := time.Now()
	resp, err := client.Do(req)
	if err != nil {
		return nil, err
	}
	defer resp.Body.Close()
	latency := time.Since(start).Milliseconds()

	body, _ := io.ReadAll(resp.Body)

	var resData map[string]interface{}
	_ = json.Unmarshal(body, &resData)

	if resp.StatusCode == http.StatusOK {
		msg := "เชื่อมต่อเซิร์ฟเวอร์ AOPOD และยืนยัน Token สำเร็จ"
		if serverMsg, ok := resData["message"].(string); ok && serverMsg != "" {
			msg = serverMsg
		}
		return map[string]interface{}{
			"status":     "success",
			"latency_ms": latency,
			"http_code":  resp.StatusCode,
			"message":    msg,
			"data":       resData,
		}, nil
	}

	if resp.StatusCode == 403 {
		msg := "Token ไม่ตรงกับโรงพยาบาลที่ระบุ (403 Mismatch)"
		if serverMsg, ok := resData["message"].(string); ok && serverMsg != "" {
			msg = serverMsg
		}
		return map[string]interface{}{
			"status":     "mismatch",
			"latency_ms": latency,
			"http_code":  resp.StatusCode,
			"message":    msg,
			"data":       resData,
		}, nil
	}

	if resp.StatusCode == 401 {
		return map[string]interface{}{
			"status":     "unauthorized",
			"latency_ms": latency,
			"http_code":  resp.StatusCode,
			"message":    "เชื่อมต่อเซิร์ฟเวอร์ได้ แต่ Token ไม่ถูกต้องหรือหมดอายุ (401 Unauthorized)",
		}, nil
	}

	return map[string]interface{}{
		"status":     "error",
		"latency_ms": latency,
		"http_code":  resp.StatusCode,
		"message":    fmt.Sprintf("เซิร์ฟเวอร์ตอบกลับรหัส %d: %s", resp.StatusCode, string(body)),
	}, nil
}

func stringsTrimRight(s, cutset string) string {
	for len(s) > 0 && s[len(s)-1] == cutset[0] {
		s = s[:len(s)-1]
	}
	return s
}

func stringsJoin(elems []string, sep string) string {
	if len(elems) == 0 {
		return ""
	}
	res := elems[0]
	for _, e := range elems[1:] {
		res += sep + e
	}
	return res
}
