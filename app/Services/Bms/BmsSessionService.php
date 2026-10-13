<?php

namespace App\Services\Bms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class BmsSessionService
{
    protected string $pasteJsonUrl = 'https://hosxp.net/phapi/PasteJSON';
    protected string $marketplaceUrl = 'https://hosxp-marketplace.bmscloud.in.th';
    protected string $aiGatewayUrl = 'https://ai-api.kube.bmscloud.in.th';

    /**
     * Resolve bms-session-id เป็นข้อมูลการเชื่อมต่อและข้อมูลผู้ใช้ HOSxP จาก PasteJSON
     */
    public function resolveSession(string $sessionId): array
    {
        $sessionId = trim($sessionId);
        if (empty($sessionId)) {
            throw new Exception("BMS Session ID ว่างเปล่า");
        }

        $cacheKey = "bms_resolved_session_{$sessionId}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($sessionId) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(12)
                    ->get($this->pasteJsonUrl, [
                        'Action' => 'GET',
                        'code' => $sessionId,
                    ]);

                if (!$response->successful()) {
                    throw new Exception("ไม่สามารถติดต่อเซิร์ฟเวอร์ BMS PasteJSON ได้ (HTTP {$response->status()})");
                }

                $data = $response->json();
                $messageCode = $data['MessageCode'] ?? null;

                if ($messageCode !== 200 && ($data['Message'] ?? '') !== 'OK') {
                    throw new Exception("BMS Session ไม่ถูกต้องหรือหมดอายุ: " . ($data['Message'] ?? 'Unknown error'));
                }

                $result = $data['result'] ?? [];
                $userInfo = $result['user_info'] ?? [];
                $expiredSecond = (int)($result['expired_second'] ?? 36000);

                $sessionData = [
                    'session_id'       => $sessionId,
                    'bms_url'          => $userInfo['bms_url'] ?? null,
                    'bms_direct_url'   => $userInfo['bms_direct_url'] ?? null,
                    'bms_session_code' => $userInfo['bms_session_code'] ?? ($result['key_value'] ?? null),
                    'hospital_code'    => $userInfo['hospital_code'] ?? '00000',
                    'doctor_code'      => $userInfo['doctor_code'] ?? '',
                    'user_cid'         => $userInfo['user_cid'] ?? null,
                    'name'             => $userInfo['name'] ?? 'ผู้ใช้งาน HOSxP',
                    'position'         => $userInfo['position'] ?? '',
                    'position_id'      => $userInfo['position_id'] ?? null,
                    'department'       => $userInfo['department'] ?? '',
                    'location'         => $userInfo['location'] ?? '',
                    'is_hr_admin'      => (bool)($userInfo['is_hr_admin'] ?? false),
                    'is_director'      => (bool)($userInfo['is_director'] ?? false),
                    'database_name'    => $userInfo['bms_database_name'] ?? 'hosxe',
                    'database_type'    => $userInfo['bms_database_type'] ?? 'MySQL',
                    'expired_second'   => $expiredSecond,
                ];

                // บันทึกลง Hospital Session Pool เพื่อให้ฟังก์ชันรายงานของ รพ. นี้เรียกใช้ต่อได้
                if (!empty($sessionData['hospital_code'])) {
                    $this->rememberHospitalSession($sessionData);
                }

                return $sessionData;
            } catch (\Throwable $e) {
                Log::error("BMS Resolve Session Error ({$sessionId}): " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * จดจำ Session ล่าสุดของโรงพยาบาลไว้ใน Cache (Session Pool)
     */
    public function rememberHospitalSession(array $sessionData): void
    {
        $hospCode = $sessionData['hospital_code'] ?? null;
        if (!$hospCode) return;

        $ttl = now()->addSeconds((int)($sessionData['expired_second'] ?? 36000));
        Cache::put("bms_hospital_pool_{$hospCode}", $sessionData, $ttl);
    }

    /**
     * ดึง Session ที่ยังใช้งานได้ของโรงพยาบาลนั้นๆ จาก Session Pool
     */
    public function getHospitalSession(string $hospCode): ?array
    {
        return Cache::get("bms_hospital_pool_{$hospCode}");
    }

    /**
     * สั่งรัน SQL ไปยัง HOSxP ของโรงพยาบาลที่ระบุโดยตรง (สะดวกมากสำหรับทำรายงาน)
     */
    public function queryHospital(string $hospCode, string $sql, array $params = []): array
    {
        $sessionData = $this->getHospitalSession($hospCode);
        if (!$sessionData) {
            throw new Exception("ไม่พบ BMS Session ที่กำลังออนไลน์ของโรงพยาบาล [{$hospCode}] กรุณาเปิดใช้งาน HOSxP เพื่อเชื่อมต่ออุโมงค์");
        }

        return $this->querySql($sessionData, $sql, $params);
    }

    /**
     * ส่งคำสั่ง SQL Query ไปยัง HOSxP ผ่าน BMS Tunnel ({bms_url}/api/sql)
     */
    public function querySql(array $sessionData, string $sql, array $params = [], ?string $marketplaceToken = null): array
    {
        $bmsUrl = rtrim($sessionData['bms_url'] ?? '', '/');
        $bmsSessionCode = $sessionData['bms_session_code'] ?? '';

        if (empty($bmsUrl) || empty($bmsSessionCode)) {
            throw new Exception("ข้อมูล URL หรือ Session Code สำหรับ BMS Tunnel ไม่สมบูรณ์");
        }

        $formattedParams = [];
        foreach ($params as $key => $val) {
            $formattedParams[$key] = [
                'value' => (string)$val,
                'value_type' => is_numeric($val) ? 'integer' : 'string',
            ];
        }

        $payload = [
            'sql' => $sql,
            'app' => 'AOPOD.A-EMR',
            'params' => (object)$formattedParams,
        ];

        if ($marketplaceToken) {
            $payload['bms_access_token'] = $marketplaceToken;
        }

        // ยิงผ่าน Tunnel URL
        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => "Bearer {$bmsSessionCode}",
                'Accept'        => 'application/json',
                'User-Agent'    => 'AOPOD-Client',
            ])
            ->timeout(25)
            ->post("{$bmsUrl}/api/sql", $payload);

        if (!$response->successful()) {
            // Fallback: ลองยิง direct URL ถ้ากำลังรันอยู่ในเครื่องเดียวกับ HOSxP
            if (!empty($sessionData['bms_direct_url'])) {
                $directUrl = rtrim($sessionData['bms_direct_url'], '/');
                $directResponse = Http::withoutVerifying()
                    ->withHeaders([
                        'Authorization' => "Bearer {$bmsSessionCode}",
                        'Accept'        => 'application/json',
                    ])
                    ->timeout(10)
                    ->post("{$directUrl}/api/sql", $payload);

                if ($directResponse->successful() && ($directResponse->json()['MessageCode'] ?? null) === 200) {
                    return $directResponse->json()['data'] ?? [];
                }
            }

            throw new Exception("BMS Tunnel Error (HTTP {$response->status()}): " . $response->body());
        }

        $result = $response->json();
        if (($result['MessageCode'] ?? 200) !== 200) {
            throw new Exception("BMS SQL Execution Error: " . ($result['Message'] ?? 'Unknown Error'));
        }

        return $result['data'] ?? [];
    }

    /**
     * ดึงข้อมูลผ่าน BMS REST Resource API ({bms_url}/api/rest/{table})
     */
    public function restGet(array $sessionData, string $table, ?string $id = null, array $queryParams = []): array
    {
        $bmsUrl = rtrim($sessionData['bms_url'] ?? '', '/');
        $bmsSessionCode = $sessionData['bms_session_code'] ?? '';
        $path = $id ? "{$bmsUrl}/api/rest/{$table}/{$id}" : "{$bmsUrl}/api/rest/{$table}";

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => "Bearer {$bmsSessionCode}",
                'Accept'        => 'application/json',
            ])
            ->timeout(20)
            ->get($path, $queryParams);

        if (!$response->successful()) {
            throw new Exception("BMS REST API Error (HTTP {$response->status()}): " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * เรียกใช้ HOSxP Server Function เช่น get_serialnumber ({bms_url}/api/function)
     */
    public function callFunction(array $sessionData, string $functionName, array $params = []): array
    {
        $bmsUrl = rtrim($sessionData['bms_url'] ?? '', '/');
        $bmsSessionCode = $sessionData['bms_session_code'] ?? '';

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => "Bearer {$bmsSessionCode}",
            ])
            ->timeout(15)
            ->get("{$bmsUrl}/api/function", array_merge(['name' => $functionName], $params));

        if (!$response->successful()) {
            throw new Exception("BMS Function Error: " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    /**
     * ตรวจสอบสถานะความพร้อมของอุโมงค์ Tunnel ({bms_url}/api/Status)
     */
    public function checkTunnelStatus(array $sessionData): bool
    {
        $bmsUrl = rtrim($sessionData['bms_url'] ?? '', '/');
        try {
            $response = Http::withoutVerifying()->timeout(5)->get("{$bmsUrl}/api/Status");
            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * เรียกใช้งาน BMS Cloud AI: ค้นหารหัส ICD-11 ทางการแพทย์
     */
    public function lookupIcd11(string $sessionId, string $query): array
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['Authorization' => "Bearer {$sessionId}"])
                ->timeout(12)
                ->get("{$this->aiGatewayUrl}/v1/icd11/lookup", [
                    'q' => $query,
                    'limit' => 10,
                ]);

            return $response->json()['data'] ?? [];
        } catch (\Throwable $e) {
            Log::warning("BMS AI ICD11 lookup failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * ค้นหา CID 13 หลักของผู้ป่วยจาก HOSxP ผ่าน BMS Tunnel โดยใช้ HN หรือ VN
     */
    public function findPatientCid(array $sessionData, ?string $hn = null, ?string $vn = null): ?string
    {
        if (empty($sessionData['bms_url']) || empty($sessionData['bms_session_code'])) {
            return null;
        }

        try {
            if (!empty($hn)) {
                $cleanHn = trim($hn);
                $rows = $this->querySql(
                    $sessionData,
                    "SELECT cid FROM patient WHERE hn = :hn LIMIT 1",
                    ['hn' => $cleanHn]
                );
                if (!empty($rows[0]['cid'])) {
                    $cid = preg_replace('/\D/', '', $rows[0]['cid']);
                    if (strlen($cid) === 13) {
                        return $cid;
                    }
                }
            }

            if (!empty($vn)) {
                $cleanVn = trim($vn);
                $rows = $this->querySql(
                    $sessionData,
                    "SELECT p.cid FROM ovst o LEFT JOIN patient p ON p.hn = o.hn WHERE o.vn = :vn LIMIT 1",
                    ['vn' => $cleanVn]
                );
                if (!empty($rows[0]['cid'])) {
                    $cid = preg_replace('/\D/', '', $rows[0]['cid']);
                    if (strlen($cid) === 13) {
                        return $cid;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("BMS findPatientCid failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * ค้นหาเลขบัตร ปชช. (CID 13 หลัก) ของแพทย์จาก HOSxP
     */
    public function findDoctorCid(array $sessionData): ?string
    {
        // 1. ถ้าใน session มี user_cid ส่งมาตรงๆ อยู่แล้ว ให้ใช้ค่านั้นได้ทันที
        if (!empty($sessionData['user_cid'])) {
            $cid = preg_replace('/\D/', '', $sessionData['user_cid']);
            if (strlen($cid) === 13) return $cid;
        }

        if (empty($sessionData['bms_url']) || empty($sessionData['bms_session_code'])) {
            return null;
        }

        $doctorCode = trim($sessionData['doctor_code'] ?? '');
        $rawName = trim($sessionData['name'] ?? '');
        $cleanName = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.|นพ\.|พญ\.|ทพ\.|ภก\.|ดร\.)\s*/u', '', $rawName);

        try {
            if (!empty($doctorCode)) {
                $rows = $this->querySql(
                    $sessionData,
                    "SELECT cid FROM doctor WHERE code = :code LIMIT 1",
                    ['code' => $doctorCode]
                );
                if (!empty($rows[0]['cid'])) {
                    $cid = preg_replace('/\D/', '', $rows[0]['cid']);
                    if (strlen($cid) === 13) return $cid;
                }
            }

            if (!empty($cleanName)) {
                $rows = $this->querySql(
                    $sessionData,
                    "SELECT cid FROM doctor WHERE name LIKE :name LIMIT 1",
                    ['name' => '%' . $cleanName . '%']
                );
                if (!empty($rows[0]['cid'])) {
                    $cid = preg_replace('/\D/', '', $rows[0]['cid']);
                    if (strlen($cid) === 13) return $cid;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("BMS findDoctorCid failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * ตรวจสอบว่าผู้ใช้งาน HOSxP มีสิทธิ์เข้าถึงระบบหรือโมดูลนั้นๆ หรือไม่
     * โดยอ้างอิงนโยบายจากไฟล์ docs/bms_allowed_modules.json
     *
     * @param array $sessionData ข้อมูลผู้ใช้จาก BMS Session
     * @param string $moduleName ชื่อโมดูล เช่น 'aemr', 'reports', 'death_data', 'birth_data'
     * @return bool
     */
    public function canAccessModule(array $sessionData, string $moduleName = 'aemr'): bool
    {
        $result = $this->verifyDoctorEligibility($sessionData, $moduleName);
        return !empty($result['allowed']);
    }

    /**
     * ตรวจสอบสิทธิ์ผู้ใช้งานจากตาราง doctor (ต้อง active = Y)
     * และตรวจสอบ provider_id_position ตามที่กำหนดใน docs/bms_allowed_modules.json
     *
     * @param array $sessionData
     * @param string $moduleName
     * @return array
     */
    public function verifyDoctorEligibility(array $sessionData, string $moduleName = 'aemr'): array
    {
        $configPath = base_path('docs/bms_allowed_modules.json');
        if (!file_exists($configPath)) {
            $configPath = base_path('docs/bms_allowed_positions.json');
        }
        $config = file_exists($configPath) ? (json_decode(file_get_contents($configPath), true) ?: []) : [];
        $moduleConfig = $config['modules'][$moduleName] ?? [];

        $doctorCode = trim($sessionData['doctor_code'] ?? '');
        $userCid = preg_replace('/\D/', '', $sessionData['user_cid'] ?? '');

        $doctor = null;
        if (!empty($sessionData['bms_url']) && !empty($sessionData['bms_session_code'])) {
            try {
                if (!empty($doctorCode)) {
                    $rows = $this->querySql($sessionData, "
                        SELECT code, name, cid, active, provider_id_position, provider_id_name, position_id
                        FROM doctor 
                        WHERE code = :code
                        LIMIT 1
                    ", ['code' => $doctorCode]);
                    if (!empty($rows[0])) {
                        $doctor = $rows[0];
                    }
                }

                if (!$doctor && !empty($userCid) && strlen($userCid) === 13) {
                    $rows = $this->querySql($sessionData, "
                        SELECT code, name, cid, active, provider_id_position, provider_id_name, position_id
                        FROM doctor 
                        WHERE cid = :cid
                        LIMIT 1
                    ", ['cid' => $userCid]);
                    if (!empty($rows[0])) {
                        $doctor = $rows[0];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("BMS verifyDoctorEligibility query error: " . $e->getMessage());
            }
        }

        // 1. ตรวจสอบสถานะ active ในตาราง doctor
        if ($doctor) {
            $isActive = strtoupper(trim($doctor['active'] ?? '')) === 'Y';
            if (!$isActive) {
                return [
                    'allowed' => false,
                    'reason'  => "สถานะผู้ใช้งานในระบบ HOSxP ถูกระงับ (ตาราง doctor active = '{$doctor['active']}')",
                    'doctor'  => $doctor,
                ];
            }

            $position = (!empty($doctor['provider_id_position']) && !$this->isScrambledThai($doctor['provider_id_position']))
                ? trim($doctor['provider_id_position']) 
                : trim($sessionData['position'] ?? '');
            $name = $this->resolveCleanName($sessionData, $doctor);
            $cid = !empty($doctor['cid']) ? preg_replace('/\D/', '', $doctor['cid']) : $userCid;
            $code = $doctor['code'] ?? $doctorCode;
        } else {
            $position = trim($sessionData['position'] ?? '');
            $name = $this->resolveCleanName($sessionData, null);
            $cid = $userCid;
            $code = $doctorCode;
        }

        // 2. ตรวจสอบกับกลุ่มตำแหน่งที่อนุญาต (allowed_positions: Whitelist Approach)
        $allowedPositions = $moduleConfig['allowed_positions'] 
            ?? $moduleConfig['allowed_keywords'] 
            ?? $moduleConfig['allowed_provider_positions'] 
            ?? [];

        $isMatch = !empty($allowedPositions) && preg_match('/(' . implode('|', array_map('preg_quote', $allowedPositions)) . ')/u', $position . ' ' . $name);

        if (!$isMatch) {
            return [
                'allowed'  => false,
                'reason'   => "(ตำแหน่งของคุณ: {$position})",
                'doctor'   => $doctor,
                'position' => $position,
            ];
        }

        return [
            'allowed'     => true,
            'reason'      => 'OK',
            'doctor'      => $doctor,
            'doctor_code' => $code,
            'name'        => $name,
            'position'    => $position,
            'cid'         => $cid,
        ];
    }

    /**
     * ดึงชื่อผู้ใช้งานที่ถูกต้อง โดยป้องกันปัญหาชื่อเพี้ยนจากการ Encrypt/Masking ในตาราง doctor ของ HOSxP
     */
    public function resolveCleanName(array $sessionData, ?array $doctor = null): string
    {
        $sessionName = trim($sessionData['name'] ?? '');
        $providerName = trim($doctor['provider_id_name'] ?? '');
        $doctorName = trim($doctor['name'] ?? '');

        // 1. หากชื่อจาก BMS Session ถูกต้อง ไม่ว่าง ไม่ใช่ placeholder และไม่เพี้ยน ➔ ใช้เป็นอันดับ 1 เสมอ (เพราะเป็นชื่อจริงของผู้ล็อกอินขณะนั้น)
        if (!empty($sessionName) && $sessionName !== 'ผู้ใช้งาน HOSxP' && !$this->isScrambledThai($sessionName)) {
            return $sessionName;
        }

        // 2. หากใน Session ไม่มีชื่อ แต่มีชื่อจริงจาก Provider ID ที่ไม่เพี้ยน
        if (!empty($providerName) && !$this->isScrambledThai($providerName)) {
            return $providerName;
        }

        // 3. หากในตาราง doctor.name ไม่ได้ถูกเข้ารหัส/Masking
        if (!empty($doctorName) && !$this->isScrambledThai($doctorName)) {
            return $doctorName;
        }

        // 4. ค่าเริ่มต้น
        return (!empty($sessionName) && !$this->isScrambledThai($sessionName)) ? $sessionName : 'ผู้ใช้งาน HOSxP';
    }

    /**
     * ตรวจสอบว่าข้อความชื่อมีลักษณะถูกเข้ารหัส/Masking หรือเพี้ยนในฐานข้อมูล HOSxP หรือไม่
     */
    public function isScrambledThai(?string $text): bool
    {
        if (empty($text)) return false;
        $text = trim($text);

        // ตัวอักษรที่ไม่ปรากฏในชื่อคนไทยปัจจุบัน (ฃ, ฅ, ฦ, ๆ) หรือสระซ้อนผิดไวยากรณ์ (เเ, เโ, โเ)
        if (preg_match('/[ฃฅฦๆ]/u', $text) || preg_match('/[เแโใไ]{2,}/u', $text)) {
            return true;
        }

        return false;
    }
}
