package scheduler

import (
	"fmt"
	"time"

	"aopod-agent/config"
	"github.com/robfig/cron/v3"
)

var (
	cronRunner *cron.Cron
)

// StartCronScheduler starts background recurring cron jobs.
func StartCronScheduler() {
	if cronRunner != nil {
		cronRunner.Stop()
	}

	cronRunner = cron.New()
	cfg := config.Get()

	// Compute cron expression based on the 3 simple choices: Daily, Hourly, Minute
	var cronExpr string
	var desc string

	switch cfg.Schedule.Type {
	case "hourly":
		hours := cfg.Schedule.IntervalHours
		if hours <= 0 {
			hours = 1
		}
		cronExpr = fmt.Sprintf("0 */%d * * *", hours)
		desc = fmt.Sprintf("ทุกๆ %d ชั่วโมง", hours)

	case "minute":
		mins := cfg.Schedule.IntervalMins
		if mins <= 0 {
			mins = 15
		}
		cronExpr = fmt.Sprintf("*/%d * * * *", mins)
		desc = fmt.Sprintf("ทุกๆ %d นาที", mins)

	case "daily":
		fallthrough
	default:
		h := cfg.Schedule.DailyHour
		m := cfg.Schedule.DailyMinute
		if h < 0 || h > 23 {
			h = 2
		}
		if m < 0 || m > 59 {
			m = 0
		}
		cronExpr = fmt.Sprintf("%d %d * * *", m, h)
		desc = fmt.Sprintf("ทุกวัน เวลา %02d:%02d น.", h, m)
	}

	// 1. Unified Periodic Sync (Syncs Bed, OPD, IPD together)
	_, err := cronRunner.AddFunc(cronExpr, func() {
		daysBack := cfg.Schedule.SyncDaysBack
		if daysBack <= 0 {
			daysBack = 30
		}
		startDate := time.Now().AddDate(0, 0, -daysBack).Format("2006-01-02")
		endDate := time.Now().Format("2006-01-02")
		AddLog("INFO", fmt.Sprintf("[Schedule] เริ่มต้นส่งข้อมูลประจำรอบ (%s) ช่วงวันที่ %s ถึง %s", desc, startDate, endDate))
		_, _ = PerformSync(startDate, endDate)
	})
	if err != nil {
		AddLog("ERROR", fmt.Sprintf("[Schedule] ตั้งเวลารอบส่งข้อมูลล้มเหลว: %v", err))
	}

	// 2. Heartbeat & Remote Task Polling (Every 1 minute)
	_, _ = cronRunner.AddFunc("*/1 * * * *", func() {
		SendHeartbeat(GetLastSyncSummary())
		PollRemoteTasks()
	})

	cronRunner.Start()
	AddLog("INFO", fmt.Sprintf("ระบบตั้งเวลาส่งข้อมูลอัตโนมัติ (Schedule) เริ่มทำงานแล้ว: %s (Cron: %s)", desc, cronExpr))

	// Send initial heartbeat and poll remote tasks immediately upon startup
	go func() {
		time.Sleep(500 * time.Millisecond)
		SendHeartbeat(GetLastSyncSummary())
		PollRemoteTasks()
	}()
}

// StopCronScheduler stops the active scheduler.
func StopCronScheduler() {
	if cronRunner != nil {
		cronRunner.Stop()
		AddLog("INFO", "ระบบตั้งเวลาอัตโนมัติหยุดทำงานแล้ว")
	}
}
