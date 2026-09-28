<?php

namespace App\Services;

use App\Models\Hospital;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmrService
{
    /**
     * Search patient medical history by CID across all hospital AOPOD-Agents (Zero-Port Architecture).
     *
     * @param string $cid 13-digit citizen ID
     * @return array
     */
    public function searchPatientByCid(string $cid): array
    {
        $cleanCid = preg_replace('/\D/', '', $cid);
        if (strlen($cleanCid) !== 13) {
            return [
                'success' => false,
                'message' => 'เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            ];
        }

        $startTime = microtime(true);

        // Query all active hospital agents in parallel via Zero-Port reverse polling
        $resultsFromHospitals = $this->queryHospitalAgents($cleanCid);

        if (empty($resultsFromHospitals)) {
            return [
                'success' => true,
                'found' => false,
                'message' => 'ไม่พบข้อมูลประวัติการรักษาด้วยเลขประจำตัวประชาชนนี้ หรือ AOPOD-Agent ปลายทางยังไม่ได้เชื่อมต่อระบบ',
                'hospital' => ['code' => '', 'name' => 'โรงพยาบาลในเครือข่าย'],
                'hospitals' => [],
                'latency_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }

        // Aggregate patient info, allergies, clinics, and visits across all hospitals
        $aggregated = $this->aggregateMultiHospitalData($resultsFromHospitals);
        $aggregated['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        return $aggregated;
    }

    /**
     * Aggregate patient data from multiple hospital agents.
     */
    protected function aggregateMultiHospitalData(array $hospitalDataList): array
    {
        $primary = $hospitalDataList[0];
        $allVisits = [];
        $allAllergies = [];
        $allClinics = [];
        $hospitalsSummary = [];
        $hospitalHns = [];
        $seenHospCodes = [];
        $seenVisits = [];
        $seenAllergies = [];
        $seenClinics = [];

        foreach ($hospitalDataList as $hData) {
            $hCode = $hData['hospital']['code'] ?? 'UNKNOWN';
            $hName = $hData['hospital']['name'] ?? 'โรงพยาบาลในเครือข่าย';
            $vCount = count($hData['visits'] ?? []);

            if (!isset($seenHospCodes[$hCode])) {
                $seenHospCodes[$hCode] = true;
                $hospitalsSummary[] = [
                    'code' => $hCode,
                    'name' => $hName,
                    'visit_count' => $vCount,
                ];

                if (!empty($hData['patient']['hn'])) {
                    $hospitalHns[$hCode] = $hData['patient']['hn'];
                }
            }

            // Merge visits with deduplication
            if (!empty($hData['visits'])) {
                foreach ($hData['visits'] as $v) {
                    $vObj = is_array($v) ? (object)$v : $v;
                    $vHospCode = $vObj->hospital_code ?? $hCode;
                    $vHospName = $vObj->hospital_name ?? $hName;
                    $vKey = $vHospCode . '_' . ($vObj->vn ?? '');
                    
                    if (isset($seenVisits[$vKey])) {
                        continue;
                    }
                    $seenVisits[$vKey] = true;
                    $vObj->hospital_code = $vHospCode;
                    $vObj->hospital_name = $vHospName;
                    $allVisits[] = $vObj;
                }
            }

            // Merge allergies with deduplication
            if (!empty($hData['allergies'])) {
                foreach ($hData['allergies'] as $al) {
                    $alObj = is_array($al) ? (object)$al : $al;
                    $alHospCode = $alObj->hospital_code ?? $hCode;
                    $alHospName = $alObj->hospital_name ?? $hName;
                    $alKey = $alHospCode . '_' . ($alObj->agent ?? '');
                    
                    if (isset($seenAllergies[$alKey])) {
                        continue;
                    }
                    $seenAllergies[$alKey] = true;
                    $alObj->hospital_code = $alHospCode;
                    $alObj->hospital_name = $alHospName;
                    $allAllergies[] = $alObj;
                }
            }

            // Merge chronic clinics with deduplication
            if (!empty($hData['clinics'])) {
                foreach ($hData['clinics'] as $cl) {
                    $clObj = is_array($cl) ? (object)$cl : $cl;
                    $clHospCode = $clObj->hospital_code ?? $hCode;
                    $clHospName = $clObj->hospital_name ?? $hName;
                    $clKey = $clHospCode . '_' . ($clObj->clinic_name ?? '');
                    
                    if (isset($seenClinics[$clKey])) {
                        continue;
                    }
                    $seenClinics[$clKey] = true;
                    $clObj->hospital_code = $clHospCode;
                    $clObj->hospital_name = $clHospName;
                    $allClinics[] = $clObj;
                }
            }
        }

        // Sort all visits chronologically descending (newest first)
        usort($allVisits, function ($a, $b) {
            $dateA = ($a->vstdate ?? '') . ' ' . ($a->vsttime ?? '');
            $dateB = ($b->vstdate ?? '') . ' ' . ($b->vsttime ?? '');
            return strcmp($dateB, $dateA);
        });

        // Update visit counts in summary
        foreach ($hospitalsSummary as &$hs) {
            $code = $hs['code'];
            $hs['visit_count'] = count(array_filter($allVisits, fn($v) => ($v->hospital_code ?? '') === $code));
        }

        return [
            'success' => true,
            'found' => true,
            'hospital' => $primary['hospital'],
            'hospitals' => $hospitalsSummary,
            'patient' => array_merge($primary['patient'], [
                'hospital_hns' => $hospitalHns,
            ]),
            'allergies' => $allAllergies,
            'clinics' => $allClinics,
            'visits' => $allVisits,
            'total_visits_found' => count($allVisits),
        ];
    }

    /**
     * Query all active Hospital Agents via Zero-Port reverse task queue and fast long-poll pickup.
     */
    protected function queryHospitalAgents(string $cleanCid): array
    {
        $results = [];
        try {
            $hospitals = Hospital::where('is_active', true)->get();
            if ($hospitals->isEmpty()) {
                return $results;
            }

            $batchId = 'emr_pt_' . time() . '_' . Str::random(8);
            $pendingHospcodes = [];

            // 1. Dispatch reverse tasks into cache for all active hospital agents
            foreach ($hospitals as $hosp) {
                Cache::put("agent_emr_task_{$hosp->hospcode}", [
                    'task_id'       => $batchId,
                    'type'          => 'patient_search',
                    'cid'           => $cleanCid,
                    'hospital_code' => $hosp->hospcode,
                    'timestamp'     => microtime(true),
                ], 25);

                $pendingHospcodes[$hosp->hospcode] = $hosp;
            }

            // 2. Fast wait loop checking for agent results (up to 2.5 seconds, step 35ms)
            $maxWaitMs = 2500;
            $intervalMs = 35;
            $elapsedMs = 0;
            $seenHospitalCodes = [];

            while ($elapsedMs < $maxWaitMs && !empty($pendingHospcodes)) {
                foreach ($pendingHospcodes as $hCode => $hospObj) {
                    $resultKey = "agent_emr_result_{$batchId}_{$hCode}";
                    $cached = Cache::get($resultKey);
                    if ($cached !== null) {
                        unset($pendingHospcodes[$hCode]);
                        if (!empty($cached['found']) && !empty($cached['data'])) {
                            $data = $cached['data'];
                            $retHospCode = $data['hospital_code'] ?? $hCode;

                            if (!isset($seenHospitalCodes[$retHospCode])) {
                                $seenHospitalCodes[$retHospCode] = true;
                                $matchedHosp = $hospitals->firstWhere('hospcode', $retHospCode) ?? $hospObj;
                                $hospName = !empty($data['hospital_name']) ? $data['hospital_name'] : ($matchedHosp->name ?? 'โรงพยาบาลในเครือข่าย');

                                $results[] = [
                                    'success' => true,
                                    'found'   => true,
                                    'hospital' => [
                                        'code' => $retHospCode,
                                        'name' => $hospName,
                                    ],
                                    'patient' => [
                                        'hn'        => $data['hn'] ?? '',
                                        'cid'       => $data['cid'] ?? $cleanCid,
                                        'full_name' => $data['full_name'] ?? '',
                                        'sex'       => $data['sex'] ?? '',
                                        'age'       => $data['age'] ?? '',
                                        'birthday'  => $data['birthday'] ?? '',
                                        'bloodgrp'  => $data['bloodgrp'] ?? '',
                                        'pttype'    => $data['pttype'] ?? '',
                                        'address'   => $data['address'] ?? '',
                                    ],
                                    'allergies'          => $data['allergies'] ?? [],
                                    'clinics'            => $data['clinics'] ?? [],
                                    'visits'             => $data['visits'] ?? [],
                                    'total_visits_found' => $data['total_visits_found'] ?? count($data['visits'] ?? []),
                                ];
                            }
                        }
                    }
                }

                if (empty($pendingHospcodes)) {
                    break; // All active agents have already submitted their results
                }

                usleep($intervalMs * 1000);
                $elapsedMs += $intervalMs;
            }

            // 3. Fallback: If no results found via reverse task, attempt direct HTTP pool as secondary fallback
            if (empty($results) && !empty($pendingHospcodes)) {
                $responses = Http::pool(function ($pool) use ($pendingHospcodes, $cleanCid) {
                    $calls = [];
                    foreach ($pendingHospcodes as $hosp) {
                        if (!empty($hosp->agent_url)) {
                            $url = rtrim($hosp->agent_url, '/') . "/api/emr/patient?cid=" . $cleanCid;
                            $calls[] = $pool->as($hosp->hospcode)
                                            ->timeout(1)
                                            ->withToken($hosp->token_api ?? '')
                                            ->get($url);
                        }
                    }
                    return $calls;
                });

                foreach ($responses as $hospcode => $response) {
                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $data = $response->json();
                        if (!empty($data['found']) && !empty($data['data'])) {
                            $retHospCode = $data['data']['hospital_code'] ?? $hospcode;

                            if (isset($seenHospitalCodes[$retHospCode])) {
                                continue;
                            }
                            $seenHospitalCodes[$retHospCode] = true;

                            $matchedHosp = $hospitals->firstWhere('hospcode', $retHospCode) ?? $hospitals->firstWhere('hospcode', $hospcode);
                            $hospName = !empty($data['data']['hospital_name']) ? $data['data']['hospital_name'] : ($matchedHosp->name ?? 'โรงพยาบาลในเครือข่าย');

                            $results[] = [
                                'success' => true,
                                'found' => true,
                                'hospital' => [
                                    'code' => $retHospCode,
                                    'name' => $hospName,
                                ],
                                'patient' => [
                                    'hn' => $data['data']['hn'],
                                    'cid' => $data['data']['cid'],
                                    'full_name' => $data['data']['full_name'],
                                    'sex' => $data['data']['sex'],
                                    'age' => $data['data']['age'],
                                    'birthday' => $data['data']['birthday'],
                                    'bloodgrp' => $data['data']['bloodgrp'],
                                    'pttype' => $data['data']['pttype'],
                                    'address' => $data['data']['address'],
                                ],
                                'allergies' => $data['data']['allergies'] ?? [],
                                'clinics' => $data['data']['clinics'] ?? [],
                                'visits' => $data['data']['visits'] ?? [],
                                'total_visits_found' => $data['data']['total_visits_found'] ?? 0,
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning("AOPOD Agent query warning: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Get detailed medications, non-drugs, lab results, diagnoses, and procedures from AOPOD-Agent.
     *
     * @param string $vn
     * @param string|null $hospitalCode
     * @return array
     */
    public function getVisitDetail(string $vn, ?string $hospitalCode = null): array
    {
        $startTime = microtime(true);

        try {
            $hosp = null;
            if (!empty($hospitalCode)) {
                $hosp = Hospital::where('hospcode', $hospitalCode)->where('is_active', true)->first();
            }
            if (!$hosp) {
                $hosp = Hospital::where('is_active', true)->first();
            }

            $targetHospCode = $hospitalCode ?: ($hosp->hospcode ?? '10989');
            $taskId = 'emr_vn_' . time() . '_' . Str::random(8);

            // 1. Dispatch real-time reverse task to agent
            Cache::put("agent_emr_task_{$targetHospCode}", [
                'task_id'       => $taskId,
                'type'          => 'visit_detail',
                'vn'            => $vn,
                'hospital_code' => $targetHospCode,
                'timestamp'     => microtime(true),
            ], 20);

            // 2. Fast wait loop for agent response (max 2.5 seconds, step 35ms)
            $maxWaitMs = 2500;
            $intervalMs = 35;
            $elapsedMs = 0;
            $resultKey = "agent_emr_result_{$taskId}_{$targetHospCode}";

            while ($elapsedMs < $maxWaitMs) {
                $cached = Cache::get($resultKey);
                if ($cached !== null) {
                    if (!empty($cached['found']) && !empty($cached['data'])) {
                        $data = $cached['data'];
                        $data['success'] = true;
                        $data['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                        return $data;
                    }
                    if (!empty($cached['success']) && empty($cached['found'])) {
                        return [
                            'success' => false,
                            'message' => 'ไม่พบข้อมูลรายละเอียดการตรวจรักษานี้บนระบบ HOSxP',
                        ];
                    }
                    break;
                }
                usleep($intervalMs * 1000);
                $elapsedMs += $intervalMs;
            }

            // 3. Fallback to direct HTTP if available
            $agentUrl = ($hosp && $hosp->agent_url) ? $hosp->agent_url : env('AOPOD_AGENT_URL', 'http://127.0.0.1:8989');
            $url = rtrim($agentUrl, '/') . "/api/emr/visit?vn=" . urlencode($vn);

            try {
                $response = Http::timeout(1.5)->withToken($hosp->token_api ?? '')->get($url);
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $resData = $response->json();
                    if (!empty($resData['data'])) {
                        $data = $resData['data'];
                        $data['success'] = true;
                        $data['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                        return $data;
                    }
                }
            } catch (\Exception $ex) {
                // Agent HTTP unavailable, continue to local fallback
            }

            // 4. Fallback to direct local HOSxP database if connected
            $directDetail = $this->collectLocalHosxpVisitDetail($vn);
            if ($directDetail) {
                $directDetail['success'] = true;
                $directDetail['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                return $directDetail;
            }

            return [
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อ AOPOD-Agent หรือไม่พบข้อมูลการรักษาบน Agent',
            ];
        } catch (\Exception $e) {
            Log::error("A-EMR visit detail fetch error via Agent: " . $e->getMessage());

            // Try direct local HOSxP fallback as ultimate failover
            $directDetail = $this->collectLocalHosxpVisitDetail($vn);
            if ($directDetail) {
                $directDetail['success'] = true;
                $directDetail['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                return $directDetail;
            }

            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fallback: Query local HOSxP database directly if configured.
     */
    public function collectLocalHosxpVisitDetail(string $vn): ?array
    {
        try {
            $cleanVN = trim($vn);
            $an = ''; $hn = ''; $actualVN = $cleanVN;

            $ovst = DB::connection('hosxp')->selectOne("SELECT an, hn, vn FROM ovst WHERE vn = ? OR an = ? LIMIT 1", [$cleanVN, $cleanVN]);
            if ($ovst) {
                $an = $ovst->an ?? '';
                $hn = $ovst->hn ?? '';
                $actualVN = $ovst->vn ?? $cleanVN;
            }
            if (!$an || !$hn) {
                $ipt = DB::connection('hosxp')->selectOne("SELECT an, hn, vn FROM ipt WHERE an = ? OR vn = ? LIMIT 1", [$cleanVN, $cleanVN]);
                if ($ipt) {
                    $an = $ipt->an ?? $an;
                    $hn = $ipt->hn ?? $hn;
                    $actualVN = $ipt->vn ?? $actualVN;
                }
            }

            $detail = [
                'vn' => $actualVN,
                'an' => $an,
                'is_ipd' => !empty($an),
                'admdate' => '',
                'admtime' => '',
                'dchdate' => '',
                'dchtime' => '',
                'los' => 1,
                'ward_name' => '',
                'dch_type' => '',
                'dch_status' => '',
                'adm_doctor' => '',
                'dch_doctor' => '',
                'chart_status' => '',
                'drg' => '',
                'rw' => 0.0,
                'adjrw' => 0.0,
                'total_income' => 0.0,
                'paid_money' => 0.0,
                'uc_money' => 0.0,
                'medications' => [],
                'non_drugs' => [],
                'lab_results' => [],
                'diagnoses' => [],
                'procedures' => [],
                'ipd_diagnoses' => [],
            ];

            $ipdInfo = null;
            if ($an) {
                $ipdInfo = DB::connection('hosxp')->selectOne("
                    SELECT 
                        ipt.regdate, ipt.regtime, ipt.dchdate, ipt.dchtime,
                        w.name AS ward_name, doc_adm.name AS adm_doctor, COALESCE(doc_dch.name, doc_dx.name) AS dch_doctor,
                        ds.name AS dch_status, dt.name AS dch_type,
                        COALESCE(ans.drg, ipt.drg, '') AS drg,
                        COALESCE(ans.rw, ipt.rw, 0) AS rw,
                        COALESCE(ipt.adjrw, 0) AS adjrw,
                        COALESCE(ans.income, 0) AS total_income,
                        COALESCE(ans.rcpt_money, 0) AS paid_money,
                        COALESCE(ans.uc_money, 0) AS uc_money
                    FROM ipt
                    LEFT JOIN an_stat ans ON ans.an = ipt.an
                    LEFT JOIN ward w ON w.ward = ipt.ward
                    LEFT JOIN doctor doc_adm ON doc_adm.code = ipt.admdoctor
                    LEFT JOIN doctor doc_dch ON doc_dch.code = ipt.dch_doctor
                    LEFT JOIN doctor doc_dx ON doc_dx.code = ans.dx_doctor
                    LEFT JOIN dchstts ds ON ds.dchstts = ipt.dchstts
                    LEFT JOIN dchtype dt ON dt.dchtype = ipt.dchtype
                    WHERE ipt.an = ?
                    LIMIT 1
                ", [$an]);

                if ($ipdInfo) {
                    $detail['admdate'] = $ipdInfo->regdate ?? '';
                    $detail['admtime'] = $ipdInfo->regtime ?? '';
                    $detail['dchdate'] = $ipdInfo->dchdate ?? '';
                    $detail['dchtime'] = $ipdInfo->dchtime ?? '';
                    $detail['ward_name'] = $ipdInfo->ward_name ?? '';
                    $detail['adm_doctor'] = $ipdInfo->adm_doctor ?? '';
                    $detail['dch_doctor'] = $ipdInfo->dch_doctor ?? '';
                    $detail['dch_status'] = $ipdInfo->dch_status ?? '';
                    $detail['dch_type'] = $ipdInfo->dch_type ?? '';
                    $detail['drg'] = $ipdInfo->drg ?? '';
                    $detail['rw'] = (float)($ipdInfo->rw ?? 0);
                    $detail['adjrw'] = (float)($ipdInfo->adjrw ?? 0);
                    $detail['total_income'] = (float)($ipdInfo->total_income ?? 0);
                    $detail['paid_money'] = (float)($ipdInfo->paid_money ?? 0);
                    $detail['uc_money'] = (float)($ipdInfo->uc_money ?? 0);

                    if (!empty($ipdInfo->regdate)) {
                        if (!empty($ipdInfo->dchdate)) {
                            $tAdm = strtotime(substr($ipdInfo->regdate, 0, 10));
                            $tDch = strtotime(substr($ipdInfo->dchdate, 0, 10));
                            $days = max(1, (int)(($tDch - $tAdm) / 86400) + 1);
                            $detail['los'] = $days;
                        } else {
                            $detail['los'] = 1;
                        }
                    }

                    if (empty($ipdInfo->dchdate)) {
                        $detail['chart_status'] = 'กำลังนอนรักษาตัวใน รพ. (Admitted)';
                    } elseif (!empty($ipdInfo->dch_doctor) || !empty($ipdInfo->drg) || $detail['rw'] > 0) {
                        $detail['chart_status'] = 'สรุปชาร์จแล้ว (Chart Summarized)';
                    } else {
                        $detail['chart_status'] = 'จำหน่ายแล้ว (รอสรุปชาร์จ)';
                    }

                    // IPD Diagnoses
                    $ipdDiags = DB::connection('hosxp')->select("
                        SELECT 
                            id.diagtype, id.icd10, COALESCE(i.name, '') AS diag_name,
                            CASE 
                                WHEN id.diagtype = '1' THEN 'Principal Diagnosis (โรคหลัก)'
                                WHEN id.diagtype = '2' THEN 'Comorbidity (โรคร่วม)'
                                WHEN id.diagtype = '3' THEN 'Complication (โรคแทรก)'
                                WHEN id.diagtype = '4' THEN 'Other (โรคอื่น)'
                                WHEN id.diagtype = '5' THEN 'External Cause (สาเหตุภายนอก)'
                                ELSE 'อื่นๆ'
                            END AS diagtype_name
                        FROM iptdiag id
                        LEFT JOIN icd101 i ON i.code = id.icd10
                        WHERE id.an = ?
                        ORDER BY id.diagtype ASC
                    ", [$an]);
                    $detail['ipd_diagnoses'] = array_map(fn($d) => (array)$d, $ipdDiags);

                    // IPD Procedures
                    $ipdProcs = DB::connection('hosxp')->select("
                        SELECT 
                            iop.icd9, COALESCE(i9.name, '') AS proc_name, COALESCE(d.name, '') AS doctor_name,
                            CASE 
                                WHEN iop.oper_type = 1 THEN 'Principal Procedure (หัตถการหลัก IPD)'
                                WHEN iop.oper_type = 2 THEN 'Secondary Procedure (หัตถการรอง IPD)'
                                ELSE 'หัตถการ IPD'
                            END AS proctype_name,
                            'IPD' AS category
                        FROM iptoprt iop
                        LEFT JOIN icd9cm1 i9 ON i9.code = iop.icd9
                        LEFT JOIN doctor d ON d.code = iop.doctor
                        WHERE iop.an = ?
                        ORDER BY iop.oper_type ASC
                    ", [$an]);
                    $detail['procedures'] = array_map(fn($p) => (array)$p, $ipdProcs);
                }
            }

            // Medications
            $meds = DB::connection('hosxp')->select("
                SELECT 
                    d.name AS drug_name,
                    SUM(op.qty) AS qty,
                    COALESCE(d.units, '') AS units,
                    MAX(COALESCE(du.name1, '')) AS usage1,
                    MAX(COALESCE(du.name2, '')) AS usage2,
                    MAX(COALESCE(du.name3, '')) AS usage3,
                    MAX(COALESCE(op.sp_use, COALESCE(sp.name1, ''))) AS sp_use,
                    COALESCE(SUM(op.sum_price), 0) AS sum_price,
                    CASE 
                        WHEN op.item_type = 'H' THEN 'ยากลับบ้าน (Home Meds)'
                        WHEN op.an IS NOT NULL AND op.an != '' THEN 'ยาระหว่างนอน รพ.'
                        ELSE 'ยาผู้ป่วยนอก (OPD)'
                    END AS med_category,
                    COALESCE(MIN(COALESCE(op.rxdate, op.vstdate)), '') AS first_date,
                    COALESCE(MAX(COALESCE(op.rxdate, op.vstdate)), '') AS last_date,
                    COUNT(DISTINCT COALESCE(op.rxdate, op.vstdate)) AS days_count
                FROM opitemrece op
                JOIN drugitems d ON d.icode = op.icode
                LEFT JOIN drugusage du ON du.drugusage = op.drugusage
                LEFT JOIN sp_use sp ON sp.sp_use = op.sp_use
                WHERE (op.vn = ? OR (op.an IS NOT NULL AND op.an != '' AND op.an = ?))
                GROUP BY d.icode, d.name, d.units, med_category
                ORDER BY CASE WHEN med_category = 'ยากลับบ้าน (Home Meds)' THEN 1 WHEN med_category = 'ยาผู้ป่วยนอก (OPD)' THEN 2 ELSE 3 END ASC, d.name ASC
            ", [$actualVN, $an]);
            $detail['medications'] = array_map(fn($m) => (array)$m, $meds);

            // Non-Drug Services
            $nonDrugs = DB::connection('hosxp')->select("
                SELECT 
                    nd.name AS item_name,
                    SUM(op.qty) AS qty,
                    COALESCE(nd.unit, '') AS units,
                    COALESCE(op.unitprice, 0) AS unit_price,
                    COALESCE(SUM(op.sum_price), 0) AS sum_price,
                    CASE 
                        WHEN op.an IS NOT NULL AND op.an != '' THEN 'IPD'
                        ELSE 'OPD'
                    END AS category
                FROM opitemrece op
                JOIN nondrugitems nd ON nd.icode = op.icode
                WHERE (op.vn = ? OR (op.an IS NOT NULL AND op.an != '' AND op.an = ?))
                GROUP BY nd.icode, nd.name, nd.unit, op.unitprice, category
                ORDER BY nd.name ASC
            ", [$actualVN, $an]);
            $detail['non_drugs'] = array_map(fn($nd) => (array)$nd, $nonDrugs);

            // Labs
            // In HOSxP:
            // - lab_head.vn = vn  => OPD Lab
            // - lab_head.vn = an  => IPD Lab
            $labs = DB::connection('hosxp')->select("
                SELECT 
                    COALESCE(i.lab_items_name, 'Lab item') AS lab_name,
                    COALESCE(lo.lab_order_result, '') AS lab_result,
                    COALESCE(i.lab_items_unit, '') AS lab_unit,
                    COALESCE(i.lab_items_normal_value, '-') AS normal_value,
                    COALESCE(lh.order_date, '') AS order_date,
                    COALESCE(lh.order_time, '') AS order_time,
                    COALESCE(lh.form_name, 'ผลตรวจทั่วไป') AS lab_group,
                    CASE 
                        WHEN ? != '' AND lh.vn = ? THEN 'IPD'
                        ELSE 'OPD'
                    END AS category
                FROM lab_order lo
                JOIN lab_head lh ON lh.lab_order_number = lo.lab_order_number
                LEFT JOIN lab_items i ON i.lab_items_code = lo.lab_items_code
                WHERE (lh.vn = ? OR (? != '' AND lh.vn = ?))
                  AND lo.lab_order_result IS NOT NULL 
                  AND TRIM(lo.lab_order_result) != '' 
                  AND TRIM(lo.lab_order_result) != '-'
                ORDER BY lh.order_date DESC, lh.order_time DESC, i.lab_items_name ASC
            ", [$an, $an, $actualVN, $an, $an]);
            $detail['lab_results'] = array_map(fn($l) => (array)$l, $labs);

            // OPD Diagnoses
            $opdDiags = DB::connection('hosxp')->select("
                SELECT 
                    od.diagtype, od.icd10, COALESCE(i.name, '') AS diag_name,
                    CASE 
                        WHEN od.diagtype = '1' THEN 'Principal Diagnosis (โรคหลัก)'
                        WHEN od.diagtype = '2' THEN 'Comorbidity (โรคร่วม)'
                        WHEN od.diagtype = '3' THEN 'Complication (โรคแทรก)'
                        WHEN od.diagtype = '4' THEN 'Other (โรคอื่น)'
                        WHEN od.diagtype = '5' THEN 'External Cause (สาเหตุภายนอก)'
                        ELSE 'อื่นๆ'
                    END AS diagtype_name
                FROM ovstdiag od
                LEFT JOIN icd101 i ON i.code = od.icd10
                WHERE od.vn = ? AND (od.icd10 REGEXP '^[A-Za-z]')
                ORDER BY od.diagtype ASC
            ", [$actualVN]);
            $detail['diagnoses'] = array_map(fn($d) => (array)$d, $opdDiags);

            // OPD Procedures
            $seenProcs = [];
            foreach ($detail['procedures'] as $p) {
                $seenProcs[$p['icd9']] = true;
            }
            $opdProcs = DB::connection('hosxp')->select("
                SELECT 
                    od.icd10 AS icd9, COALESCE(i9.name, COALESCE(i10.name, '')) AS proc_name, COALESCE(d.name, '') AS doctor_name,
                    CASE 
                        WHEN od.diagtype = '1' THEN 'Principal Procedure (หัตถการหลัก)'
                        WHEN od.diagtype = '2' THEN 'Secondary Procedure (หัตถการรอง)'
                        ELSE 'หัตถการอื่น'
                    END AS proctype_name,
                    'OPD' AS category
                FROM ovstdiag od
                LEFT JOIN icd9cm1 i9 ON i9.code = od.icd10
                LEFT JOIN icd101 i10 ON i10.code = od.icd10
                LEFT JOIN doctor d ON d.code = od.doctor
                WHERE od.vn = ? AND (od.icd10 REGEXP '^[0-9]')
                ORDER BY od.diagtype ASC
            ", [$actualVN]);
            foreach ($opdProcs as $p) {
                if (!isset($seenProcs[$p->icd9])) {
                    $seenProcs[$p->icd9] = true;
                    $detail['procedures'][] = (array)$p;
                }
            }

            return $detail;
        } catch (\Throwable $e) {
            Log::warning("Local HOSxP direct query fallback error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Hospital Information from HOSxP opdconfig
     */
    protected function getHospitalInfo(): array
    {
        try {
            $info = DB::connection('hosxp')->selectOne("SELECT hospitalcode, hospitalname FROM opdconfig LIMIT 1");
            if ($info) {
                return [
                    'code' => $info->hospitalcode,
                    'name' => $info->hospitalname,
                ];
            }
        } catch (\Exception $e) {
            // fallback
        }

        return [
            'code' => '10989',
            'name' => 'รพช. หัวตะพาน',
        ];
    }
}

