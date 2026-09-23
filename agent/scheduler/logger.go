package scheduler

import (
	"fmt"
	"io"
	"log"
	"os"
	"path/filepath"
	"strings"
	"sync"
	"time"

	"aopod-agent/config"
)

var (
	logFileLock sync.Mutex
)

// GetLogsDir returns the absolute path to logs directory in Roaming AppData.
func GetLogsDir() string {
	dir := filepath.Join(config.GetConfigDir(), "logs")
	_ = os.MkdirAll(dir, 0755)
	return dir
}

// GetTodayLogPath returns the path to today's log file (e.g. logs/agent-2026-09-23.log).
func GetTodayLogPath() string {
	return filepath.Join(GetLogsDir(), fmt.Sprintf("agent-%s.log", time.Now().Format("2006-01-02")))
}

// InitLogger configures Go's standard logger to write to today's daily log file.
func InitLogger() {
	logFileLock.Lock()
	defer logFileLock.Unlock()

	todayPath := GetTodayLogPath()
	f, err := os.OpenFile(todayPath, os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)
	if err == nil {
		parentPath := filepath.Join(config.GetConfigDir(), "agent.log")
		pf, pErr := os.OpenFile(parentPath, os.O_CREATE|os.O_WRONLY|os.O_APPEND, 0666)
		if pErr == nil {
			log.SetOutput(io.MultiWriter(f, pf))
		} else {
			log.SetOutput(f)
		}
	}
}

// CleanOldLogs removes log files older than retentionDays (default 30 days / 1 month).
func CleanOldLogs(retentionDays int) int {
	if retentionDays <= 0 {
		retentionDays = 30
	}
	logsDir := GetLogsDir()
	cutoff := time.Now().AddDate(0, 0, -retentionDays)
	deletedCount := 0

	entries, err := os.ReadDir(logsDir)
	if err != nil {
		return 0
	}

	for _, entry := range entries {
		if entry.IsDir() || !strings.HasSuffix(entry.Name(), ".log") {
			continue
		}
		info, err := entry.Info()
		if err != nil {
			continue
		}
		if info.ModTime().Before(cutoff) {
			_ = os.Remove(filepath.Join(logsDir, entry.Name()))
			deletedCount++
		}
	}

	// Also prune parent agent.log if it is older than cutoff
	parentPath := filepath.Join(config.GetConfigDir(), "agent.log")
	if info, err := os.Stat(parentPath); err == nil {
		if info.ModTime().Before(cutoff) {
			_ = os.WriteFile(parentPath, []byte(""), 0644)
		}
	}

	return deletedCount
}

// ClearLogs clears all in-memory logs and deletes/truncates disk log files.
func ClearLogs() error {
	logMutex.Lock()
	defer logMutex.Unlock()

	recentLogs = []LogEntry{
		{
			Timestamp: time.Now().Format("2006-01-02 15:04:05"),
			Level:     "INFO",
			Message:   "ล้างประวัติ Log ในระบบเรียบร้อยแล้ว",
		},
	}

	logsDir := GetLogsDir()
	entries, err := os.ReadDir(logsDir)
	if err == nil {
		for _, entry := range entries {
			if strings.HasSuffix(entry.Name(), ".log") {
				_ = os.Remove(filepath.Join(logsDir, entry.Name()))
			}
		}
	}

	parentPath := filepath.Join(config.GetConfigDir(), "agent.log")
	_ = os.WriteFile(parentPath, []byte(""), 0644)

	// Re-init logger
	InitLogger()

	return nil
}
