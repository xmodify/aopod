<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\MainSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgentApiController extends Controller
{
    /**
     * Return curated ICD-10 PP Lookup list for Agent.
     */
    public function getIcd10Lookup(Request $request)
    {
        $cacheKey = 'agent_lookup_icd10_pp';
        $data = Cache::remember($cacheKey, 3600, function () {
            return \App\Http\Controllers\Web\AgentWebController::getPpIcd10List();
        });

        return response()->json([
            'status' => 'success',
            'version' => '1.0.0',
            'count' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * Return remote config and schedule for the connected Hospital Agent.
     */
    public function getConfig(Request $request)
    {
        $user = Auth::user();
        $hospcode = $user->hospcode ?? $request->query('hospcode');

        // All hospital codes in province
        $provinceHospitals = Hospital::pluck('hospcode')->filter()->values()->toArray();
        if (empty($provinceHospitals)) {
            $provinceHospitals = ['10989', '10985', '10986', '10987', '10988', '10990', '10703'];
        }

        // Check if there is a pending remote sync task for this hospital
        $pendingTask = Cache::get("agent_remote_task_{$hospcode}");

        $schedule = \App\Models\AgentSchedule::getForHospital($hospcode);

        return response()->json([
            'status' => 'success',
            'hospital_code' => $hospcode,
            'settings' => [
                'schedule' => [
                    'interval_hours'    => (int)($schedule->interval_hours ?? 1),
                    'start_minute'      => (int)($schedule->start_minute ?? 15),
                    'opd_days_back'     => (int)($schedule->opd_days_back ?? 5),
                    'ipd_days_back'     => (int)($schedule->ipd_days_back ?? 30),
                    'bed_interval_mins' => (int)($schedule->bed_interval_mins ?? 15),
                    'is_active'         => (bool)($schedule->is_active ?? true),
                ],
                'opd_cron' => MainSetting::get('agent_opd_cron', '0 */2 * * *'),
                'ipd_cron' => MainSetting::get('agent_ipd_cron', '0 */2 * * *'),
                'bed_cron' => MainSetting::get('agent_bed_cron', '*/15 * * * *'),
                'sync_days_back' => (int)($schedule->sync_days_back ?? 10),
                'chunk_size' => 200,
                'province_hospcodes' => $provinceHospitals,
                'main_sss_hospcode' => '10703',
                'latest_agent_version' => MainSetting::get('agent_latest_version', '1.0.0'),
                'agent_download_url' => url('/api/agent/download-latest'),
            ],
            'queries' => \App\Http\Controllers\Web\AgentWebController::getActiveQueries(),
            'queries_version' => MainSetting::get('agent_queries_version', '2026.09.23.2'),
            'pending_task' => $pendingTask,
        ]);
    }

    /**
     * Heartbeat endpoint for Agent to report its health and status.
     */
    public function heartbeat(Request $request)
    {
        $user = Auth::user();
        $hospcode = $user->hospcode ?? $request->input('hospcode');

        if (!$hospcode) {
            return response()->json(['message' => 'Missing hospcode'], 422);
        }

        $payload = [
            'hospcode' => $hospcode,
            'version' => $request->input('version', '1.0.0'),
            'status' => $request->input('status', 'running'),
            'db_status' => $request->input('db_status', 'connected'),
            'last_sync_opd' => $request->input('last_sync_opd'),
            'last_sync_ipd' => $request->input('last_sync_ipd'),
            'last_sync_bed' => $request->input('last_sync_bed'),
            'last_error' => $request->input('last_error'),
            'hostname' => $request->input('hostname'),
            'ip' => $request->ip(),
            'updated_at' => now()->toDateTimeString(),
        ];

        // Store live heartbeat in cache for 10 minutes
        Cache::put("agent_heartbeat_{$hospcode}", $payload, 600);

        return response()->json(['status' => 'success', 'message' => 'Heartbeat received']);
    }

    /**
     * Acknowledge/complete a remote sync task.
     */
    public function completeTask(Request $request)
    {
        $user = Auth::user();
        $hospcode = $user->hospcode ?? $request->input('hospcode');
        $taskId = $request->input('task_id');

        Cache::forget("agent_remote_task_{$hospcode}");

        return response()->json(['status' => 'success', 'message' => "Task {$taskId} completed"]);
    }

    /**
     * Verify Token and Hospital Code authenticity.
     */
    public function verifyToken(Request $request)
    {
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['status' => 'unauthorized', 'message' => 'Token ไม่ถูกต้องหรือไม่มีสิทธิ์ใช้งาน'], 401);
        }

        $tokenHospcode = $hospital->hospcode;
        $requestedHospcode = $request->input('hospcode');

        if ($requestedHospcode && $tokenHospcode !== $requestedHospcode) {
            $expectedHospital = Hospital::where('hospcode', $requestedHospcode)->first();
            $expectedName = $expectedHospital ? $expectedHospital->name : "รหัส {$requestedHospcode}";

            return response()->json([
                'status'              => 'mismatch',
                'matched'             => false,
                'token_hospital_code' => $tokenHospcode,
                'token_hospital_name' => $hospital->name,
                'requested_hospcode'  => $requestedHospcode,
                'message'             => "❌ Token ไม่ตรงกับโรงพยาบาล! (Token นี้เป็นของ {$hospital->name} [{$tokenHospcode}] ไม่ใช่ของ {$expectedName} [{$requestedHospcode}])",
            ], 403);
        }

        return response()->json([
            'status'        => 'success',
            'matched'       => true,
            'hospital_code' => $hospital->hospcode,
            'hospital_name' => $hospital->name,
            'message'       => "ยืนยัน Token สำเร็จสำหรับ {$hospital->name} ({$hospital->hospcode})",
        ]);
    }
}
