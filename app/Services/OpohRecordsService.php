<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * ดึงรายเคส (ระดับ record) ของแพทย์ OPOH สดจาก agent ของ รพ. ผ่านกลไก reverse long-poll เดียวกับ A-EMR
 * (Zero-Port: เซิร์ฟเวอร์วางงานใน cache, agent poll ไปรับ, ส่งผลกลับมาเก็บ cache ไม่เกิน 60 วินาที)
 * ไม่เก็บข้อมูลผู้ป่วยระบุตัวตนลงฐานข้อมูลกลาง
 */
class OpohRecordsService
{
    /**
     * ส่งงาน opoh_doctor_records ไปยัง agent ของ $hospcode แล้วรอผล
     *
     * @param array $params type, cid, licenseno, start_date, end_date, offset, limit, search, order_col, order_dir, summary
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function fetch(string $hospcode, array $params, int $maxWaitMs = 25000, string $taskType = 'opoh_doctor_records'): array
    {
        $taskId = 'opoh_rec_' . time() . '_' . Str::random(8);

        // งานเก่าที่ยังไม่มีใครรับ (เช่น agent ออฟไลน์) หมดอายุเอง
        // opoh_indiv_export ส่งพารามิเตอร์ใต้คีย์ 'indiv' (ตรงกับ AemrTask.Indiv ของ agent)
        Cache::put("agent_aemr_task_{$hospcode}", [
            'task_id'       => $taskId,
            'type'          => $taskType,
            'hospital_code' => $hospcode,
            ($taskType === 'opoh_indiv_export' ? 'indiv' : 'params') => $params,
            'timestamp'     => microtime(true),
        ], 30);

        $resultKey = "agent_aemr_result_{$taskId}_{$hospcode}";
        $intervalMs = 50;
        $elapsedMs = 0;

        while ($elapsedMs < $maxWaitMs) {
            $cached = Cache::get($resultKey);
            if ($cached !== null) {
                Cache::forget($resultKey); // ไม่เก็บข้อมูลผู้ป่วยค้างใน cache
                if (empty($cached['success'])) {
                    return [
                        'success' => false,
                        'message' => $cached['message'] ?: 'agent ดึงข้อมูลไม่สำเร็จ',
                        'data'    => null,
                    ];
                }
                return ['success' => true, 'message' => '', 'data' => $cached['data'] ?? null];
            }
            usleep($intervalMs * 1000);
            $elapsedMs += $intervalMs;
        }

        // หมดเวลา: ถอนงานที่ยังไม่ถูกรับออกจากคิว
        $queued = Cache::get("agent_aemr_task_{$hospcode}");
        if ($queued && ($queued['task_id'] ?? null) === $taskId) {
            Cache::forget("agent_aemr_task_{$hospcode}");
        }

        return [
            'success' => false,
            'message' => 'agent ของโรงพยาบาลไม่ตอบสนอง (ออฟไลน์ หรือยังเป็นเวอร์ชันเก่าที่ไม่รองรับรายเคส)',
            'data'    => null,
        ];
    }
}
