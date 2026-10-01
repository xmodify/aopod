<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmrAccessLog;
use App\Models\Hospital;
use App\Services\EmrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmrWebController extends Controller
{
    protected EmrService $emrService;

    public function __construct(EmrService $emrService)
    {
        $this->emrService = $emrService;
    }

    /**
     * Render the main A-EMR Search and Viewer page.
     */
    public function index()
    {
        if (!auth()->check() || !auth()->user()->canAccessEmr()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงระบบ A-EMR กรุณาติดต่อผู้ดูแลระบบ');
        }

        return view('admin.emr.index');
    }

    /**
     * Search patient medical history across hospitals / local HOSxP.
     */
    public function search(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessEmr()) {
            return response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงระบบ A-EMR'], 403);
        }

        $request->validate([
            'cid' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $startTime = microtime(true);
        $user = auth()->user();
        $cid = preg_replace('/\D/', '', $request->input('cid'));
        $reason = $request->input('reason', 'การตรวจรักษาทั่วไป');

        if (strlen($cid) !== 13) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุเลขประจำตัวประชาชน 13 หลักให้ถูกต้อง',
            ], 422);
        }

        $result = $this->emrService->searchPatientByCid($cid);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        // Determine log status
        $status = 'ERROR';
        $targetHn = null;
        $targetHospCode = null;
        $recordCount = 0;

        if (!empty($result['success'])) {
            if (!empty($result['found'])) {
                $status = 'SUCCESS';
                $targetHn = $result['patient']['hn'] ?? null;
                $targetHospCode = $result['hospital']['code'] ?? null;
                $recordCount = $result['total_visits_found'] ?? count($result['visits'] ?? []);
            } else {
                $status = 'NOT_FOUND';
            }
        }

        // Save Audit Trail Log to Database
        try {
            EmrAccessLog::create([
                'user_id'          => $user->id,
                'username'         => $user->email ?? $user->name,
                'user_name'        => $user->name,
                'user_role'        => $user->role ?? 'staff',
                'user_hospcode'    => $user->hospcode,
                'provider_id'      => $user->provider_id,
                'action'           => 'SEARCH_CID',
                'target_cid'       => $cid,
                'target_hn'        => $targetHn,
                'target_hospcode'  => $targetHospCode,
                'reason'           => $reason,
                'ip_address'       => $request->ip() ?: '127.0.0.1',
                'user_agent'       => substr($request->userAgent() ?? '', 0, 500),
                'request_url'      => substr($request->fullUrl(), 0, 500),
                'http_method'      => $request->method(),
                'status'           => $status,
                'http_status_code' => 200,
                'response_time_ms' => $latencyMs,
                'record_count'     => $recordCount,
                'error_message'    => empty($result['success']) ? ($result['message'] ?? 'Search failed') : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to write EMR audit log: " . $e->getMessage());
        }

        // Also write to standard application log
        Log::info("[A-EMR Audit] User: {$user->name} (ID: {$user->id}, Role: {$user->role}) searched CID: {$cid}, Status: {$status}, Reason: {$reason}, Latency: {$latencyMs}ms");

        return response()->json($result);
    }

    /**
     * Get detailed medications, lab results, and diagnoses for a specific visit.
     */
    public function visitDetail(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessEmr()) {
            return response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงระบบ A-EMR'], 403);
        }

        $request->validate([
            'vn' => 'required|string',
            'hospital_code' => 'nullable|string',
            'cid' => 'nullable|string',
        ]);

        $startTime = microtime(true);
        $user = auth()->user();
        $vn = $request->input('vn');
        $hospitalCode = $request->input('hospital_code');
        $cid = preg_replace('/\D/', '', $request->input('cid', ''));

        $result = $this->emrService->getVisitDetail($vn, $hospitalCode);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        $status = (!empty($result['success']) && (!isset($result['found']) || $result['found'] === true)) ? 'SUCCESS' : 'NOT_FOUND';
        if (empty($result['success'])) {
            $status = 'ERROR';
        }

        // Save Audit Trail Log for visit detail
        try {
            EmrAccessLog::create([
                'user_id'          => $user->id,
                'username'         => $user->email ?? $user->name,
                'user_name'        => $user->name,
                'user_role'        => $user->role ?? 'staff',
                'user_hospcode'    => $user->hospcode,
                'provider_id'      => $user->provider_id,
                'action'           => 'VIEW_VISIT_DETAIL',
                'target_cid'       => $cid ?: null,
                'target_vn'        => $vn,
                'target_hospcode'  => $hospitalCode,
                'reason'           => 'ดูรายละเอียดใบสั่งยาและผลตรวจ (Visit: ' . $vn . ')',
                'ip_address'       => $request->ip() ?: '127.0.0.1',
                'user_agent'       => substr($request->userAgent() ?? '', 0, 500),
                'request_url'      => substr($request->fullUrl(), 0, 500),
                'http_method'      => $request->method(),
                'status'           => $status,
                'http_status_code' => 200,
                'response_time_ms' => $latencyMs,
                'record_count'     => count($result['medications'] ?? []) + count($result['lab_results'] ?? []),
                'error_message'    => empty($result['success']) ? ($result['message'] ?? 'Fetch failed') : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to write EMR visit audit log: " . $e->getMessage());
        }

        return response()->json($result);
    }

    /**
     * View Audit Logs for A-EMR (Strictly Admin Only).
     */
    public function logs(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่มีสิทธิ์เข้าถึงประวัติการใช้งาน Audit Log');
        }

        $query = EmrAccessLog::query();

        // Filters
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $cleanCid = preg_replace('/\D/', '', $search);
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('target_vn', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
                if (strlen($cleanCid) >= 4) {
                    $q->orWhere('target_cid', 'like', "%{$cleanCid}%");
                }
            });
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($hospcode = $request->input('hospcode')) {
            $query->where(function ($q) use ($hospcode) {
                $q->where('user_hospcode', $hospcode)
                  ->orWhere('target_hospcode', $hospcode);
            });
        }

        // Date range filtering
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [
                Carbon::parse($dateStart)->startOfDay(),
                Carbon::parse($dateEnd)->endOfDay(),
            ]);
        } elseif ($dateStart) {
            $query->where('created_at', '>=', Carbon::parse($dateStart)->startOfDay());
        } elseif ($dateEnd) {
            $query->where('created_at', '<=', Carbon::parse($dateEnd)->endOfDay());
        }

        // Handle CSV Export
        if ($request->input('export') === 'csv') {
            return $this->exportLogsCsv($query);
        }

        // Statistics Summary
        $todayCount = EmrAccessLog::whereDate('created_at', Carbon::today())->count();
        $monthCount = EmrAccessLog::whereMonth('created_at', Carbon::now()->month)
                                  ->whereYear('created_at', Carbon::now()->year)
                                  ->count();
        $uniqueUsersCount = EmrAccessLog::distinct('user_id')->count('user_id');
        $avgLatency = round((float)EmrAccessLog::avg('response_time_ms'), 1);

        $logs = $query->orderBy('id', 'desc')->paginate(30)->withQueryString();
        $hospitals = Hospital::orderBy('hospcode')->get();

        return view('admin.emr.logs', compact(
            'logs',
            'hospitals',
            'todayCount',
            'monthCount',
            'uniqueUsersCount',
            'avgLatency'
        ));
    }

    /**
     * Export Audit Logs to CSV for compliance audits.
     */
    protected function exportLogsCsv($query)
    {
        $fileName = 'a_emr_audit_logs_' . date('Y-m-d_His') . '.csv';
        $records = $query->orderBy('id', 'desc')->limit(5000)->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'ID',
                'วันและเวลา',
                'ผู้ใช้งาน',
                'บทบาท',
                'รหัส รพ. ผู้ใช้',
                'การกระทำ (Action)',
                'เลขบัตร ปชช. (CID)',
                'เลข VN/AN',
                'รหัส รพ. ปลายทาง',
                'วัตถุประสงค์ (PDPA Reason)',
                'IP Address',
                'สถานะ',
                'HTTP Code',
                'ความเร็ว (ms)',
                'จำนวนรายการ',
            ]);

            foreach ($records as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '',
                    $log->user_name . ' (' . ($log->username ?? '-') . ')',
                    $log->user_role,
                    $log->user_hospcode,
                    $log->action,
                    $log->target_cid ? "'" . $log->target_cid : '-',
                    $log->target_vn ?? '-',
                    $log->target_hospcode ?? '-',
                    $log->reason,
                    $log->ip_address,
                    $log->status,
                    $log->http_status_code,
                    $log->response_time_ms,
                    $log->record_count,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
