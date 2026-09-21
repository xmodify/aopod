package updater

import (
	"crypto/tls"
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"syscall"
	"time"

	"aopod-agent/config"
	"aopod-agent/singleinstance"
)

var LogFunc func(level, msg string)

func logMsg(level, msg string) {
	if LogFunc != nil {
		LogFunc(level, msg)
	}
}

// CleanOldBinary removes any leftover .old binary from a previous update.
func CleanOldBinary() {
	exePath, err := os.Executable()
	if err != nil {
		return
	}
	exePath, _ = filepath.EvalSymlinks(exePath)
	oldExe := exePath + ".old"
	if _, err := os.Stat(oldExe); err == nil {
		_ = os.Remove(oldExe)
	}
}

// PerformSelfUpdate downloads the new binary from server, swaps files, and restarts the agent.
func PerformSelfUpdate(downloadURL, targetVersion, taskID string) error {
	cfg := config.Get()
	if targetVersion == "" {
		targetVersion = "latest"
	}

	logMsg("INFO", fmt.Sprintf("[Auto-Update] 🚀 ได้รับคำสั่งอัปเดตโปรแกรมเป็นเวอร์ชั่น %s (Task: %s)", targetVersion, taskID))

	if downloadURL == "" {
		if cfg.Hospital.ServerURL != "" {
			downloadURL = fmt.Sprintf("%s/api/agent/download-latest", strings.TrimRight(cfg.Hospital.ServerURL, "/"))
		} else {
			logMsg("ERROR", "[Auto-Update] ❌ ไม่พบ URL สำหรับดาวน์โหลดไฟล์อัปเดต")
			return fmt.Errorf("download URL is empty")
		}
	}

	logMsg("INFO", fmt.Sprintf("[Auto-Update] ⏳ กำลังดาวน์โหลดไฟล์ติดตั้งใหม่จาก %s ...", downloadURL))

	// 1. Download to temporary file
	tempDir := os.TempDir()
	tempExe := filepath.Join(tempDir, fmt.Sprintf("AOPOD-Agent-update-%d.exe", time.Now().Unix()))

	client := &http.Client{
		Timeout: 90 * time.Second,
		Transport: &http.Transport{
			TLSClientConfig: &tls.Config{InsecureSkipVerify: true},
		},
	}

	req, err := http.NewRequest("GET", downloadURL, nil)
	if err != nil {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ สร้างคำขอดาวน์โหลดล้มเหลว: %v", err))
		return fmt.Errorf("create request failed: %w", err)
	}
	if cfg.Hospital.Token != "" {
		req.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)
	}

	resp, err := client.Do(req)
	if err != nil {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ ไม่สามารถเชื่อมต่อเพื่อดาวน์โหลดได้: %v", err))
		return fmt.Errorf("download request failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ เซิร์ฟเวอร์ตอบกลับรหัส HTTP %d", resp.StatusCode))
		return fmt.Errorf("download failed with HTTP status %d", resp.StatusCode)
	}

	out, err := os.Create(tempExe)
	if err != nil {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ ไม่สามารถสร้างไฟล์ชั่วคราวได้: %v", err))
		return fmt.Errorf("create temp file failed: %w", err)
	}

	written, err := io.Copy(out, resp.Body)
	_ = out.Close()
	if err != nil {
		_ = os.Remove(tempExe)
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ บันทึกไฟล์ดาวน์โหลดล้มเหลว: %v", err))
		return fmt.Errorf("save download file failed: %w", err)
	}

	if written < 1000000 { // Must be at least 1MB
		_ = os.Remove(tempExe)
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ ขนาดไฟล์ที่ดาวน์โหลดเล็กเกินไป (%d bytes) ยกเลิกการอัปเดต", written))
		return fmt.Errorf("downloaded file too small (%d bytes), update aborted", written)
	}

	// Verify Windows PE header 'MZ'
	fCheck, err := os.Open(tempExe)
	if err == nil {
		magic := make([]byte, 2)
		_, _ = fCheck.Read(magic)
		fCheck.Close()
		if string(magic) != "MZ" {
			_ = os.Remove(tempExe)
			logMsg("ERROR", "[Auto-Update] ❌ ไฟล์ที่ดาวน์โหลดไม่ใช่ไฟล์รันบน Windows (ไม่มี MZ header)")
			return fmt.Errorf("downloaded file is not a valid Windows executable")
		}
	}

	logMsg("SUCCESS", fmt.Sprintf("[Auto-Update] 📥 ดาวน์โหลดไฟล์สำเร็จ (%0.2f MB) กำลังสลับไฟล์โปรแกรม...", float64(written)/(1024*1024)))

	// 2. Acknowledge task completion before swapping and restarting
	if taskID != "" && cfg.Hospital.ServerURL != "" && cfg.Hospital.Token != "" {
		ackURL := fmt.Sprintf("%s/api/agent/task/complete", strings.TrimRight(cfg.Hospital.ServerURL, "/"))
		ackPayload := fmt.Sprintf(`{"task_id":"%s","hospcode":"%s"}`, taskID, cfg.Hospital.Code)
		ackReq, _ := http.NewRequest("POST", ackURL, strings.NewReader(ackPayload))
		ackReq.Header.Set("Content-Type", "application/json")
		ackReq.Header.Set("Authorization", "Bearer "+cfg.Hospital.Token)
		if ackResp, err := client.Do(ackReq); err == nil {
			ackResp.Body.Close()
		}
	}

	// 3. Swap running executable on Windows
	currentExe, err := os.Executable()
	if err != nil {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ ไม่สามารถระบุตำแหน่งไฟล์โปรแกรมเดิมได้: %v", err))
		return fmt.Errorf("cannot get current executable path: %w", err)
	}
	currentExe, _ = filepath.EvalSymlinks(currentExe)

	oldExe := currentExe + ".old"
	_ = os.Remove(oldExe)

	if err := os.Rename(currentExe, oldExe); err != nil {
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ เปลี่ยนชื่อไฟล์เดิมเป็น .old ล้มเหลว: %v", err))
		return fmt.Errorf("failed to rename current exe to .old: %w", err)
	}

	// Read downloaded binary data
	inData, err := os.ReadFile(tempExe)
	if err != nil {
		_ = os.Rename(oldExe, currentExe) // Rollback
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ อ่านไฟล์ดาวน์โหลดล้มเหลว: %v", err))
		return fmt.Errorf("failed to read downloaded exe: %w", err)
	}

	if err := os.WriteFile(currentExe, inData, 0755); err != nil {
		_ = os.Rename(oldExe, currentExe) // Rollback
		logMsg("ERROR", fmt.Sprintf("[Auto-Update] ❌ วางไฟล์ใหม่ทับล้มเหลว: %v", err))
		return fmt.Errorf("failed to write new exe into place: %w", err)
	}
	_ = os.Remove(tempExe)

	logMsg("SUCCESS", "[Auto-Update] ✅ สลับไฟล์โปรแกรมสำเร็จ กำลังเริ่มต้นการทำงานของ AOPOD-Agent เวอร์ชั่นใหม่...")

	// 4. Release mutex before exiting
	singleinstance.Release()

	// 5. Launch new binary via temporary batch script after 2s delay
	batPath := filepath.Join(os.TempDir(), "aopod_restart.bat")
	batContent := fmt.Sprintf("@echo off\r\ntimeout /t 2 /nobreak >nul\r\nstart \"\" \"%s\"\r\ndel \"%%~f0\"\r\n", currentExe)
	_ = os.WriteFile(batPath, []byte(batContent), 0755)

	cmd := exec.Command("cmd.exe", "/c", batPath)
	cmd.SysProcAttr = &syscall.SysProcAttr{HideWindow: true}
	_ = cmd.Start()

	// 6. Cleanly terminate old process immediately
	os.Exit(0)

	return nil
}
