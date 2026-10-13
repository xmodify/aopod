<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use App\Models\EmrAccessLog;
use App\Services\Bms\BmsSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BmsAutoLoginController extends Controller
{
    protected BmsSessionService $bmsService;

    public function __construct(BmsSessionService $bmsService)
    {
        $this->bmsService = $bmsService;
    }

    /**
     * ดึงค่า bms-session-id จาก Query String หรือ Server Parameters
     */
    protected function extractSessionId(Request $request): ?string
    {
        $keys = [
            'bms-session-id',
            'bms_session_id',
            'sessionId',
            'session_id',
            'session',
            'code',
        ];

        foreach ($keys as $k) {
            $val = $request->query($k) ?: $request->input($k);
            if (!empty($val) && is_string($val) && strlen(trim($val)) >= 8) {
                return trim($val);
            }
        }

        // Fallback: ดึงจาก QUERY_STRING ตรงๆ เผื่อโดนแปลง
        $queryString = $request->server('QUERY_STRING', '') ?: ($_SERVER['QUERY_STRING'] ?? '');
        if (!empty($queryString)) {
            if (preg_match('/(?:bms[-_]session[-_]id|session_id|session|code)=([a-zA-Z0-9\-]+)/i', $queryString, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * ดึงค่าบริบทคนไข้ (hn, vn, an, cid)
     */
    protected function extractPatientContext(Request $request): array
    {
        $pick = function (...$keys) use ($request) {
            foreach ($keys as $k) {
                $val = $request->query($k) ?: $request->input($k);
                if (!empty($val) && is_string($val) && trim($val) !== '') {
                    return trim($val);
                }
            }
            return null;
        };

        return [
            'hn'  => $pick('hn', 'HN'),
            'vn'  => $pick('vn', 'VN'),
            'an'  => $pick('an', 'AN'),
            'cid' => $pick('cid', 'CID'),
        ];
    }

    /**
     * รับคำขอเปิดระบบจาก HOSxP (ทั้ง Dashboard URL และ Patient Add-on)
     */
    public function handleLaunch(Request $request)
    {
        $sessionId = $this->extractSessionId($request);
        $context = $this->extractPatientContext($request);

        if (empty($sessionId)) {
            // หากไม่มี session ให้พาไปหน้าหลักตามปกติ
            return redirect()->to(url('web'));
        }

        try {
            // 1. Resolve Session จาก BMS PasteJSON
            $sessionData = $this->bmsService->resolveSession($sessionId);
            $rawName = trim($sessionData['name'] ?? '');
            $hospCode = $sessionData['hospital_code'] ?? '';

            // ตรวจสอบว่าโรงพยาบาลอยู่ในระบบเครือข่าย AOPOD (ตาราง hospitals) หรือไม่ ป้องกัน รพ. นอกระบบแอบใช้
            $hospital = Hospital::where('hospcode', $hospCode)->where('is_active', true)->first();
            if (!$hospital) {
                Log::warning("BMS Launch Rejected: Hospital [{$hospCode}] is not in AOPOD authorized hospitals.");
                return view('bms.error', [
                    'title'       => 'สถานพยาบาลไม่ได้รับอนุญาต',
                    'message'     => "รหัสสถานพยาบาล [{$hospCode}] ไม่อยู่ในระบบเครือข่าย AOPOD หรือถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบจังหวัด",
                    'sessionData' => $sessionData,
                ]);
            }

            // 2. ตรวจสอบสิทธิ์บุคลากรจากตาราง doctor (ต้อง active = Y) และ provider_id_position ตาม docs/bms_allowed_modules.json
            $eligibility = $this->bmsService->verifyDoctorEligibility($sessionData, 'aemr');

            if (empty($eligibility['allowed'])) {
                return view('bms.error', [
                    'title'       => 'ท่านไม่มีสิทธิ์การเข้าถึงข้อมูล A-EMR',
                    'message'     => $eligibility['reason'] ?? 'ท่านไม่มีสิทธิ์การเข้าถึงข้อมูล A-EMR',
                    'sessionData' => $sessionData,
                ]);
            }

            $clinicianName = $eligibility['name'] ?? $rawName;
            if ($this->bmsService->isScrambledThai($clinicianName)) {
                $clinicianName = $rawName ?: 'ผู้ใช้งาน HOSxP';
            }
            $clinicianPosition = $eligibility['position'] ?? ($sessionData['position'] ?? 'บุคลากรทางการแพทย์');
            $clinicianCid = $eligibility['cid'] ?? $sessionData['user_cid'];
            $clinicianDoctorCode = $eligibility['doctor_code'] ?? ($sessionData['doctor_code'] ?? '-');

            // 3. ฝากข้อมูลตัวตนไว้ใน Laravel Session (Zero-Footprint in users table)
            session([
                'is_bms_auth'     => true,
                'auth_channel'    => 'hosxp_bms',
                'bms_user_name'   => $clinicianName ?: 'ผู้ใช้งาน HOSxP',
                'bms_doctor_code' => $clinicianDoctorCode ?: '-',
                'bms_user_cid'    => $clinicianCid ?: null,
                'bms_position'    => $clinicianPosition,
                'bms_hospcode'    => $hospCode ?: '00000',
                'bms_session_id'  => $sessionId,
                'bms_login_time'  => now()->toDateTimeString(),
            ]);

            Log::info("BMS Launch: Clinician [{$clinicianName}] Position: [{$clinicianPosition}] (Hosp: {$hospCode}) verified via doctor table.");

            // 4. ตรวจสอบบริบทคนไข้ (Patient Context)
            $patientCid = $context['cid'] ?? null;

            // หากส่งมาแค่ hn หรือ vn ให้ดึง CID ผ่านอุโมงค์ BMS Tunnel ของ รพ. นั้น
            if (empty($patientCid) && (!empty($context['hn']) || !empty($context['vn']))) {
                $patientCid = $this->bmsService->findPatientCid($sessionData, $context['hn'], $context['vn']);
            }

            // เมื่อเปิดจาก HOSxP (ทั้ง Dashboard URL และ Patient Add-on) ➔ ส่งไปหน้าจอ Dedicated Clinical Viewer (ไร้ Sidebar)
            return redirect()->route('bms.emr.viewer', [
                'cid' => !empty($patientCid) ? preg_replace('/\D/', '', $patientCid) : '',
                'hn'  => $context['hn'] ?? '',
                'vn'  => $context['vn'] ?? '',
            ]);

        } catch (\Throwable $e) {
            Log::error("BMS Launch Exception: " . $e->getMessage());
            return view('bms.error', [
                'title' => 'การเชื่อมต่อ HOSxP Session ล้มเหลว',
                'message' => 'เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์ BMS Session: ' . $e->getMessage(),
                'sessionData' => null,
            ]);
        }
    }

    /**
     * ค้นหาและจับคู่ User ใน AOPOD
     */
    protected function matchUser(string $rawName, string $hospCode, array $sessionData): ?User
    {
        // 1. ลองค้นหา CID ของแพทย์จาก HOSxP แล้วจับคู่กับ users.cid
        $doctorCid = $this->bmsService->findDoctorCid($sessionData);
        if (!empty($doctorCid) && strlen($doctorCid) === 13) {
            $userByCid = User::where('cid', $doctorCid)->where('active', 'Y')->first();
            if ($userByCid) {
                return $userByCid;
            }
        }

        // 2. ลองจับคู่ด้วย ชื่อ-นามสกุล และ รหัส รพ. (hospcode)
        $cleanName = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.|นพ\.|พญ\.|ทพ\.|ภก\.|ดร\.)\s*/u', '', $rawName);
        $cleanName = trim($cleanName);

        if (!empty($cleanName)) {
            // ตรงกับชื่อ และ รหัส รพ.
            $user = User::where('hospcode', $hospCode)
                ->where('name', 'LIKE', '%' . $cleanName . '%')
                ->where('active', 'Y')
                ->first();
            if ($user) return $user;

            // ตรงกับชื่อในระบบทั้งหมด
            $userAnyHosp = User::where('name', 'LIKE', '%' . $cleanName . '%')
                ->where('active', 'Y')
                ->first();
            if ($userAnyHosp) return $userAnyHosp;
        }

        // 3. ถ้าเป็นเครื่องของ รพ. เดียวกับ Admin บัญชีหลัก หรือเปิดโหมดทดสอบ
        if (!empty($hospCode)) {
            $adminUser = User::where('hospcode', $hospCode)
                ->where('role', 'admin')
                ->where('active', 'Y')
                ->first();
            if ($adminUser) {
                Log::info("BMS Launch: Fallback to hospital admin [{$adminUser->name}] for hospcode [{$hospCode}].");
                return $adminUser;
            }
        }

        return null;
    }
}
