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

            $response = Http::timeout(2)->withToken($hosp->token_api ?? '')->get($url);
            if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                $resData = $response->json();
                if (!empty($resData['data'])) {
                    $data = $resData['data'];
                    $data['success'] = true;
                    $data['latency_ms'] = round((microtime(true) - $startTime) * 1000, 2);
                    return $data;
                }
            }

            return [
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อ AOPOD-Agent หรือไม่พบข้อมูลการรักษาบน Agent',
            ];
        } catch (\Exception $e) {
            Log::error("A-EMR visit detail fetch error via Agent: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ Agent: ' . $e->getMessage(),
            ];
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

