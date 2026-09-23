package scheduler

import (
	"fmt"
	"time"

	"aopod-agent/config"
	"github.com/robfig/cron/v3"
)

var (
	cronRunner            *cron.Cron
	currentRemoteSchedule *RemoteSchedule
)

type RemoteSchedule struct {
	IntervalHours   int  `json:"interval_hours"`
	StartMinute     int  `json:"start_minute"`
	OpdDaysBack     int  `json:"opd_days_back"`
	IpdDaysBack     int  `json:"ipd_days_back"`
	BedIntervalMins int  `json:"bed_interval_mins"`
	IsActive        bool `json:"is_active"`
}

// ApplyRemoteSchedule updates scheduler timings from central server policy.
func ApplyRemoteSchedule(sched *RemoteSchedule) {
	if sched == nil {
		return
	}

	if currentRemoteSchedule != nil &&
		currentRemoteSchedule.IntervalHours == sched.IntervalHours &&
		currentRemoteSchedule.StartMinute == sched.StartMinute &&
		currentRemoteSchedule.OpdDaysBack == sched.OpdDaysBack &&
		currentRemoteSchedule.IpdDaysBack == sched.IpdDaysBack &&
		currentRemoteSchedule.BedIntervalMins == sched.BedIntervalMins &&
		currentRemoteSchedule.IsActive == sched.IsActive {
		return // No change
	}

	currentRemoteSchedule = sched

	// Update local in-memory config
	cfg := config.Get()
	cfg.Schedule.IntervalHours = sched.IntervalHours
	cfg.Schedule.StartMinute = sched.StartMinute
	cfg.Schedule.OpdDaysBack = sched.OpdDaysBack
	cfg.Schedule.IpdDaysBack = sched.IpdDaysBack
	cfg.Schedule.BedIntervalMins = sched.BedIntervalMins
	cfg.Schedule.IsActive = sched.IsActive
	_ = config.Update(cfg)

	AddLog("INFO", fmt.Sprintf("[Schedule] 🔄 ซิงค์รอบเวลาส่งข้อมูลจากเซิร์ฟเวอร์: หมวด 1 ทุก %d ชม. (นาทีที่ %02d, OPD %d วัน, IPD %d วัน) | หมวด 2 เตียง ทุก %d นาที | Active: %v",
		sched.IntervalHours, sched.StartMinute, sched.OpdDaysBack, sched.IpdDaysBack, sched.BedIntervalMins, sched.IsActive))
	StartCronScheduler()
}

// StartCronScheduler starts background recurring cron jobs.
func StartCronScheduler() {
	if cronRunner != nil {
		cronRunner.Stop()
	}

	cronRunner = cron.New()
	cfg := config.Get()

	if cfg.Schedule.IsActive {
		// 1. หมวดที่ 1: รอบส่งข้อมูล (รายชั่วโมง)
		hours := cfg.Schedule.IntervalHours
		if hours <= 0 {
			hours = 1
		}
		startMin := cfg.Schedule.StartMinute
		if startMin < 0 || startMin > 59 {
			startMin = 15
		}

		var hourlyCron string
		if hours == 1 {
			hourlyCron = fmt.Sprintf("%d * * * *", startMin)
		} else {
			hourlyCron = fmt.Sprintf("%d */%d * * *", startMin, hours)
		}

		opdDaysBack := cfg.Schedule.OpdDaysBack
		if opdDaysBack <= 0 {
			opdDaysBack = 5
		}
		ipdDaysBack := cfg.Schedule.IpdDaysBack
		if ipdDaysBack <= 0 {
			ipdDaysBack = 30
		}

		hourlyDesc := fmt.Sprintf("ทุกๆ %d ชม. (ที่นาทีที่ %02d)", hours, startMin)
		_, err := cronRunner.AddFunc(hourlyCron, func() {
			AddLog("INFO", fmt.Sprintf("[Schedule] เริ่มต้นส่งข้อมูลประจำรอบรายชั่วโมง (%s)...", hourlyDesc))
			_, _ = PerformHourlySync(opdDaysBack, ipdDaysBack)
		})
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("[Schedule] ตั้งเวลารายชั่วโมงล้มเหลว: %v", err))
		} else {
			AddLog("INFO", fmt.Sprintf("หมวดที่ 1 (รายชั่วโมง) เริ่มทำงาน: %s (Cron: %s)", hourlyDesc, hourlyCron))
		}

		// 2. หมวดที่ 2: รอบส่งข้อมูล (รายนาที - สถานะเตียง)
		bedMins := cfg.Schedule.BedIntervalMins
		if bedMins <= 0 {
			bedMins = 15
		}
		bedCron := fmt.Sprintf("*/%d * * * *", bedMins)
		_, err = cronRunner.AddFunc(bedCron, func() {
			_, _ = PerformBedSync()
		})
		if err != nil {
			AddLog("ERROR", fmt.Sprintf("[Schedule] ตั้งเวลารายนาที (เตียง) ล้มเหลว: %v", err))
		} else {
			AddLog("INFO", fmt.Sprintf("หมวดที่ 2 (รายนาที - เตียง) เริ่มทำงาน: ทุกๆ %d นาที (Cron: %s)", bedMins, bedCron))
		}
	} else {
		AddLog("WARNING", "ระบบส่งข้อมูลอัตโนมัติ (Schedule) ปิดใช้งานอยู่ (Disabled)")
	}

	// 3. Heartbeat & Remote Task Polling (Every 1 minute always)
	_, _ = cronRunner.AddFunc("*/1 * * * *", func() {
		SendHeartbeat(GetLastSyncSummary())
		PollRemoteTasks()
	})

	// 4. Daily log cleanup for files older than 30 days (1 month retention) - runs at 01:00 AM every day
	_, _ = cronRunner.AddFunc("0 1 * * *", func() {
		if deleted := CleanOldLogs(30); deleted > 0 {
			AddLog("INFO", fmt.Sprintf("ลบไฟล์ Log เก่ากว่า 30 วัน จำนวน %d ไฟล์เรียบร้อยแล้ว", deleted))
		}
	})

	cronRunner.Start()

	// Initial heartbeat upon startup & initial log cleanup
	go func() {
		time.Sleep(500 * time.Millisecond)
		CleanOldLogs(30)
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
