<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\EmrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $cid = preg_replace('/\D/', '', $request->input('cid'));

        if (strlen($cid) !== 13) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุเลขประจำตัวประชาชน 13 หลักให้ถูกต้อง',
            ], 422);
        }

        // PDPA Audit Trail Log
        $user = auth()->user();
        $reason = $request->input('reason', 'การตรวจรักษาทั่วไป');
        Log::info("[A-EMR Audit] User ID: {$user->id} ({$user->name}) accessed EMR for CID: {$cid}, Reason: {$reason}");

        $result = $this->emrService->searchPatientByCid($cid);

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
        ]);

        $vn = $request->input('vn');
        $hospitalCode = $request->input('hospital_code');
        $result = $this->emrService->getVisitDetail($vn, $hospitalCode);

        return response()->json($result);
    }
}
