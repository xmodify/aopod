<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmartEmrService
{
    /**
     * Base URL for the Smart EMR API.
     */
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.smartemr.url', env('SMART_EMR_URL', 'http://192.168.16.13:8080/api/smartemr')), '/');
    }

    /**
     * Get the configured base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Query hospitals where the patient has records in the Health Zone.
     *
     * @param string $cid 13-digit citizen ID
     * @return array|null Returns ['hospitals' => [...], 'profile' => [...]] or null
     */
    public function searchHospitals(string $cid): ?array
    {
        $cleanCid = preg_replace('/\D/', '', $cid);
        if (strlen($cleanCid) !== 13) {
            return null;
        }

        try {
            $response = Http::timeout(2.5)->get("{$this->baseUrl}/query/hospital/{$cleanCid}");
            if (!$response->successful()) {
                Log::warning("Smart EMR hospital query failed [HTTP {$response->status()}]: {$this->baseUrl}/query/hospital/{$cleanCid}");
                return null;
            }

            $json = $response->json();
            if (empty($json['ok']) || empty($json['data'])) {
                return null;
            }

            return [
                'hospitals' => $json['data'] ?? [],
                'profile'   => $json['profile_data'][0] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::warning("Smart EMR query exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get patient visits for a specific hospital from Smart EMR.
     *
     * @param string $hospcode 5-digit hospital code
     * @param string $cid 13-digit citizen ID
     * @return array
     */
    public function getVisits(string $hospcode, string $cid): array
    {
        $cleanCid = preg_replace('/\D/', '', $cid);
        try {
            $response = Http::timeout(3.0)->get("{$this->baseUrl}/query/service/{$hospcode}/{$cleanCid}");
            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            if (is_array($data)) {
                return $data;
            }
            if (is_array($data['data'] ?? null)) {
                return $data['data'];
            }
            return [];
        } catch (\Throwable $e) {
            Log::warning("Smart EMR visits query exception for hospcode {$hospcode}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get detailed clinical profile for a specific visit (medications, diagnosis, vitals).
     *
     * @param string $hospcode
     * @param string $hn
     * @param string $vn
     * @return array|null
     */
    public function getServiceProfile(string $hospcode, string $hn, string $vn): ?array
    {
        try {
            $response = Http::timeout(3.5)->get("{$this->baseUrl}/query/service-profile/{$hospcode}/{$hn}/{$vn}");
            if (!$response->successful()) {
                return null;
            }

            $json = $response->json();
            if (!is_array($json)) {
                return null;
            }

            return $json;
        } catch (\Throwable $e) {
            Log::warning("Smart EMR service-profile query exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send Audit Log to Smart EMR.
     * In Stealth / Ghost Mode (default), sending logs to the external Smart EMR server is disabled
     * so that NO records or traces are created in the external Smart EMR database.
     *
     * @param string $userName Full name of clinician (e.g. "นายศิริฤกษ์ คณาดี")
     * @param string $cid 13-digit citizen ID
     * @return bool
     */
    public function sendAuditLog(string $userName, string $cid): bool
    {
        // Stealth / Ghost Mode: Completely disabled (zero trace on external server)
        if (!config('services.smartemr.send_logs', env('SMART_EMR_SEND_LOGS', false))) {
            return true;
        }

        try {
            $cleanCid = preg_replace('/\D/', '', $cid);
            $cleanUser = trim($userName) ?: 'ผู้ใช้งานระบบ HOSxP';

            $payload = [
                'request_user' => $cleanUser,
                'log_detail'   => "Search CID : {$cleanCid}",
            ];

            // Send non-blocking with 1 second timeout
            $res = Http::timeout(1.0)->post("{$this->baseUrl}/query/saveLogs", $payload);
            return $res->successful();
        } catch (\Throwable $e) {
            // Silently ignore log errors
            return false;
        }
    }

    /**
     * Format a Smart EMR service profile into AOPOD's standard visit detail array.
     *
     * @param array $profile Raw profile data from Smart EMR
     * @param string $vn Visit number
     * @param string $hospCode Hospital code
     * @param string|null $hospName Hospital name
     * @return array
     */
    public function formatAsAopodVisitDetail(array $profile, string $vn, string $hospCode, ?string $hospName = null): array
    {
        $vDetail = $profile['visit_detail'] ?? [];
        $vSigns = $vDetail['vital_sign'] ?? [];
        $rawDrugs = $profile['drugs'] ?? [];
        $rawDiags = $profile['diagnosis'] ?? [];
        $rawProcs = $profile['procedures'] ?? [];
        $rawLabs = $profile['labs'] ?? [];

        // 1. Map medications
        $medications = [];
        foreach ($rawDrugs as $d) {
            $medications[] = [
                'drug_name'    => $d['drug_name'] ?? '-',
                'qty'          => (float)($d['qty'] ?? 1),
                'units'        => $d['unit'] ?? '',
                'usage1'       => $d['drug_usage'] ?? '',
                'usage2'       => '',
                'usage3'       => '',
                'sp_use'       => '',
                'sum_price'    => 0.0,
                'med_category' => 'ยาผู้ป่วยนอก (OPD)',
                'first_date'   => !empty($d['date_serv']) ? substr($d['date_serv'], 0, 10) : '',
                'last_date'    => !empty($d['date_serv']) ? substr($d['date_serv'], 0, 10) : '',
                'days_count'   => 1,
            ];
        }

        // 2. Map diagnoses
        $diagnoses = [];
        foreach ($rawDiags as $diag) {
            $typeId = (string)($diag['diagtype_id'] ?? '1');
            $diagnoses[] = [
                'icd10'         => trim($diag['icd_code'] ?? ''),
                'diag_name'     => trim($diag['icd_name'] ?? ''),
                'diagtype'      => $typeId,
                'diagtype_name' => $typeId === '1' ? 'Principal Diagnosis (โรคหลัก)' : 'Comorbidity (โรคร่วม)',
                'doctor_name'   => 'แพทย์ รพ. ต้นทาง',
            ];
        }

        // 3. Map procedures
        $procedures = [];
        foreach ($rawProcs as $proc) {
            $procedures[] = [
                'icd9'          => $proc['icd9'] ?? ($proc['proc_code'] ?? '-'),
                'proc_name'     => $proc['proc_name'] ?? '-',
                'proctype_name' => 'หัตถการ',
                'doctor_name'   => '',
                'category'      => 'OPD',
            ];
        }

        // 4. Map lab results
        $labResults = [];
        foreach ($rawLabs as $lab) {
            $labResults[] = [
                'lab_name'   => $lab['lab_name'] ?? ($lab['test_name'] ?? '-'),
                'lab_result' => $lab['lab_result'] ?? ($lab['result'] ?? '-'),
                'unit'       => $lab['unit'] ?? '',
                'ref_range'  => $lab['ref_range'] ?? '',
                'flag'       => '',
                'order_date' => !empty($lab['date_serv']) ? substr($lab['date_serv'], 0, 10) : '',
            ];
        }

        return [
            'vn'             => $vn,
            'an'             => null,
            'is_ipd'         => false,
            'hospital_code'  => $hospCode,
            'hospital_name'  => $hospName ?? 'โรงพยาบาลในเขตสุขภาพที่ 10',
            'is_smart_emr'   => true,
            'source'         => 'smart_emr',
            'source_label'   => 'Smart EMR เขตสุขภาพที่ 10',
            'cc'             => $vDetail['cc'] ?? '',
            'pi'             => $vDetail['pi'] ?? '',
            'ph'             => $vDetail['ph'] ?? '',
            'pe'             => $vDetail['pe'] ?? '',
            'bps'            => (int)($vSigns['sbp'] ?? 0),
            'bpd'            => (int)($vSigns['dbp'] ?? 0),
            'pulse'          => (int)($vSigns['pr'] ?? 0),
            'temperature'    => (float)($vSigns['bt'] ?? 0),
            'rr'             => (int)($vSigns['rr'] ?? 0),
            'bw'             => (float)($vSigns['wt'] ?? 0),
            'height'         => (float)($vSigns['ht'] ?? 0),
            'bmi'            => (float)($vSigns['bmi'] ?? 0),
            'o2sat'          => (int)($vSigns['o2sat'] ?? 0),
            'admdate'        => '',
            'admtime'        => '',
            'dchdate'        => '',
            'dchtime'        => '',
            'los'            => 1,
            'ward_name'      => '',
            'dch_type'       => '',
            'dch_status'     => '',
            'adm_doctor'     => '',
            'dch_doctor'     => '',
            'chart_status'   => '',
            'drg'            => '',
            'rw'             => 0.0,
            'adjrw'          => 0.0,
            'total_income'   => 0.0,
            'paid_money'     => 0.0,
            'uc_money'       => 0.0,
            'medications'    => $medications,
            'non_drugs'      => [],
            'lab_results'    => $labResults,
            'diagnoses'      => $diagnoses,
            'procedures'     => $procedures,
            'ipd_diagnoses'  => [],
        ];
    }
}

