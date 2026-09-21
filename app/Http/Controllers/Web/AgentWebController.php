<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Opd;
use App\Models\Ipd;
use App\Models\MainSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AgentWebController extends Controller
{
    /**
     * Show Agent Management Dashboard for Admin.
     */
    public function index()
    {
        $hospitals = Hospital::orderBy('hospcode')->get();

        $agentList = [];
        foreach ($hospitals as $hosp) {
            $hcode = $hosp->hospcode ?? $hosp->hcode;
            
            // Get live heartbeat from cache
            $heartbeat = Cache::get("agent_heartbeat_{$hcode}");
            
            // Check last OPD / IPD record date in DB
            $lastOpd = Opd::where('hospcode', $hcode)->orderBy('vstdate', 'desc')->first(['vstdate', 'updated_at']);
            $lastIpd = Ipd::where('hospcode', $hcode)->orderBy('dchdate', 'desc')->first(['dchdate', 'updated_at']);

            // Get active Sanctum Token
            $tokenObj = $hosp->tokens()->latest()->first();

            // Status online if heartbeat received in last 2.5 minutes (150 seconds)
            $isOnline = false;
            if ($heartbeat && !empty($heartbeat['updated_at'])) {
                $lastTime = strtotime($heartbeat['updated_at']);
                if ((time() - $lastTime) < 150) {
                    $isOnline = true;
                }
            }

            $agentList[] = [
                'hospital'      => $hosp,
                'hcode'         => $hcode,
                'name'          => $hosp->name ?? $hosp->hospname ?? $hcode,
                'is_online'     => $isOnline,
                'heartbeat'     => $heartbeat,
                'has_token'     => !empty($hosp->token_api) || ($tokenObj !== null),
                'token_api'     => $hosp->token_api ?? ($tokenObj->token ?? ''),
                'token_id'      => $tokenObj->id ?? null,
                'last_opd_date' => $lastOpd->vstdate ?? null,
                'last_opd_sync' => $lastOpd->updated_at ?? null,
                'last_ipd_date' => $lastIpd->dchdate ?? null,
                'last_ipd_sync' => $lastIpd->updated_at ?? null,
                'pending_task'  => Cache::get("agent_remote_task_{$hcode}"),
            ];
        }

        $settings = [
            'opd_cron'       => MainSetting::get('agent_opd_cron', '0 */2 * * *'),
            'ipd_cron'       => MainSetting::get('agent_ipd_cron', '0 */2 * * *'),
            'bed_cron'       => MainSetting::get('agent_bed_cron', '*/15 * * * *'),
            'sync_days_back' => MainSetting::get('agent_sync_days_back', 10),
        ];

        $queries = self::getActiveQueries();
        $queriesVersion = MainSetting::get('agent_queries_version', '2026.09.22.1');
        $ppIcd10List = self::getPpIcd10List();

        return view('admin.agents', compact('agentList', 'settings', 'queries', 'queriesVersion', 'ppIcd10List'));
    }

    /**
     * Get active PP ICD-10 list (stored in main_settings as single source of truth).
     */
    public static function getPpIcd10List(): array
    {
        $setting = MainSetting::get('agent_lookup_icd10_pp');
        if ($setting) {
            $arr = json_decode($setting, true);
            if (is_array($arr) && count($arr) > 0) {
                return $arr;
            }
        }

        // Initialize with system defaults if not set yet
        $defaults = self::getDefaultPpIcd10List();
        MainSetting::set('agent_lookup_icd10_pp', json_encode($defaults));
        return $defaults;
    }

    /**
     * Return default PP ICD-10 codes list.
     */
    public static function getDefaultPpIcd10List(): array
    {
        $jsonPath = base_path('docs/lookup_icd10.json');
        if (file_exists($jsonPath)) {
            $raw = json_decode(file_get_contents($jsonPath), true);
            if (is_array($raw)) {
                return array_column($raw, 'icd10');
            }
        }

        return ['Z00', 'Z000', 'Z001', 'Z010', 'Z100', 'Z300', 'Z301', 'Z302', 'Z303', 'Z304', 'Z305', 'Z308', 'Z309'];
    }

    /**
     * Update PP ICD-10 lookup list in main_settings.
     */
    public function updatePpIcd10(Request $request)
    {
        $rawCodes = $request->input('codes', '');
        $tokens = preg_split('/[\r\n,;]+/', $rawCodes);
        $cleanCodes = [];
        foreach ($tokens as $t) {
            $code = strtoupper(trim($t));
            if (!empty($code) && !in_array($code, $cleanCodes)) {
                $cleanCodes[] = $code;
            }
        }

        if (empty($cleanCodes)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'กรุณาระบุรหัสโรค ICD-10 อย่างน้อย 1 รหัส',
            ], 422);
        }

        MainSetting::set('agent_lookup_icd10_pp', json_encode($cleanCodes));
        Cache::forget('agent_lookup_icd10_pp');

        return response()->json([
            'status'  => 'success',
            'count'   => count($cleanCodes),
            'codes'   => $cleanCodes,
            'message' => 'บันทึกรายการรหัสโรคกลุ่ม PP เรียบร้อยแล้ว (จำนวน ' . count($cleanCodes) . ' รหัส)',
        ]);
    }

    /**
     * Reset PP ICD-10 lookup list in main_settings to system default.
     */
    public function resetPpIcd10()
    {
        $defaults = self::getDefaultPpIcd10List();

        MainSetting::set('agent_lookup_icd10_pp', json_encode($defaults));
        Cache::forget('agent_lookup_icd10_pp');

        return response()->json([
            'status'  => 'success',
            'count'   => count($defaults),
            'codes'   => $defaults,
            'message' => 'คืนค่ารายการรหัสโรคกลุ่ม PP เป็นค่ามาตรฐานระบบเรียบร้อยแล้ว (จำนวน ' . count($defaults) . ' รหัส)',
        ]);
    }

    /**
     * Get active queries from DB with defaults.
     */
    public static function getActiveQueries(): array
    {
        $defaults = self::getDefaultQueries();
        return [
            'opd'       => MainSetting::get('agent_query_opd', $defaults['opd']),
            'ipd'       => MainSetting::get('agent_query_ipd', $defaults['ipd']),
            'bed_total' => MainSetting::get('agent_query_bed_total', $defaults['bed_total']),
            'bed_dep'   => MainSetting::get('agent_query_bed_dep', $defaults['bed_dep']),
        ];
    }

    /**
     * Update dynamic SQL queries from Admin.
     */
    public function updateQueries(Request $request)
    {
        $request->validate([
            'query_opd'       => 'required|string',
            'query_ipd'       => 'required|string',
            'query_bed_total' => 'required|string',
            'query_bed_dep'   => 'required|string',
        ]);

        MainSetting::set('agent_query_opd', $request->input('query_opd'));
        MainSetting::set('agent_query_ipd', $request->input('query_ipd'));
        MainSetting::set('agent_query_bed_total', $request->input('query_bed_total'));
        MainSetting::set('agent_query_bed_dep', $request->input('query_bed_dep'));

        $newVersion = 'v' . date('Ymd_His');
        MainSetting::set('agent_queries_version', $newVersion);

        return response()->json([
            'status'  => 'success',
            'version' => $newVersion,
            'message' => 'บันทึกคำสั่ง SQL ส่วนกลางเรียบร้อยแล้ว Agent ทุก รพ. จะดึงคำสั่งใหม่ไปใช้โดยอัตโนมัติ (Version: ' . $newVersion . ')',
        ]);
    }

    /**
     * Reset SQL queries to standard defaults.
     */
    public function resetQueries()
    {
        $defaults = self::getDefaultQueries();
        MainSetting::set('agent_query_opd', $defaults['opd']);
        MainSetting::set('agent_query_ipd', $defaults['ipd']);
        MainSetting::set('agent_query_bed_total', $defaults['bed_total']);
        MainSetting::set('agent_query_bed_dep', $defaults['bed_dep']);

        $newVersion = 'v' . date('Ymd_His') . '_default';
        MainSetting::set('agent_queries_version', $newVersion);

        return response()->json([
            'status'  => 'success',
            'version' => $newVersion,
            'queries' => $defaults,
            'message' => 'คืนค่าคำสั่ง SQL มาตรฐานเรียบร้อยแล้ว',
        ]);
    }

    /**
     * Return standard built-in SQL queries.
     */
    public static function getDefaultQueries(): array
    {
        return [
            'opd' => <<<'SQL'
SELECT 
	a.vstdate,
	COUNT(DISTINCT a.hn) AS hn_total,
	COUNT(a.vn) AS visit_total,
	SUM(CASE WHEN a.diagtype = 'OP' THEN 1 ELSE 0 END) AS visit_total_op,
	SUM(CASE WHEN a.diagtype = 'PP' THEN 1 ELSE 0 END) AS visit_total_pp,
	SUM(CASE WHEN a.endpoint = 'Y' THEN 1 ELSE 0 END) AS visit_endpoint,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN 1 ELSE 0 END) AS visit_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_sss,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_fss,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS visit_stp,
	SUM(CASE WHEN (a.paidst IN ('01','03') OR a.hipdata_code IN ('A1','A9')) THEN 1 ELSE 0 END) AS visit_pay,
	COUNT(DISTINCT CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.healthmed = 'Y' THEN a.vn END) AS visit_ucs_healthmed,
	COUNT(DISTINCT CASE WHEN a.healthmed = 'Y' THEN a.vn END) AS visit_healthmed,
	COUNT(DISTINCT CASE WHEN a.dent = 'Y' THEN a.vn END) AS visit_dent,
	COUNT(DISTINCT CASE WHEN a.physic = 'Y' THEN a.vn END) AS visit_physic,
	COUNT(DISTINCT CASE WHEN a.anc = 'Y' THEN a.vn END) AS visit_anc,
	COUNT(DISTINCT CASE WHEN a.telehealth = 'Y' THEN a.vn END) AS visit_telehealth,
	COALESCE(ma_booking.cnt, 0) AS visit_moph_oapp_booking,
	COUNT(DISTINCT CASE WHEN a.moph_oapp = 'Y' THEN a.cid END) AS visit_moph_oapp,
	COUNT(DISTINCT CASE WHEN a.referout_inprov = 'Y' THEN a.vn END) AS visit_referout_inprov,
	COUNT(DISTINCT CASE WHEN a.referout_outprov = 'Y' THEN a.vn END) AS visit_referout_outprov,
	COUNT(DISTINCT CASE WHEN a.referout_inprov_ipd = 'Y' THEN a.vn END) AS visit_referout_inprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referout_outprov_ipd = 'Y' THEN a.vn END) AS visit_referout_outprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referin_inprov = 'Y' THEN a.vn END) AS visit_referin_inprov,
	COUNT(DISTINCT CASE WHEN a.referin_outprov = 'Y' THEN a.vn END) AS visit_referin_outprov,
	COUNT(DISTINCT CASE WHEN a.referin_inprov_ipd = 'Y' THEN a.vn END) AS visit_referin_inprov_ipd,
	COUNT(DISTINCT CASE WHEN a.referin_outprov_ipd = 'Y' THEN a.vn END) AS visit_referin_outprov_ipd,
	COALESCE(rb.visit_referback_inprov, 0) AS visit_referback_inprov,
	COALESCE(rb.visit_referback_outprov, 0) AS visit_referback_outprov,
	COALESCE(op.visit_operation, 0) AS visit_operation,
	SUM(a.income) AS inc_total, 
	SUM(a.inc03) AS inc_lab_total, 
	SUM(a.inc12) AS inc_drug_total,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.incup = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_incup,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.inprov = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_inprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.income ELSE 0 END) AS inc_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.inc03 ELSE 0 END) AS inc_lab_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('UCS','WEL','DIS') AND a.paidst NOT IN ('01','03') AND a.outprov = 'Y' THEN a.inc12 ELSE 0 END) AS inc_drug_ucs_outprov,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('OFC') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_ofc,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BKK') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_bkk,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('BMT') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_bmt,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_sss,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_sss,
	SUM(CASE WHEN a.hipdata_code IN ('SSS','SSI') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_sss,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('LGO') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_lgo,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_fss,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_fss,
	SUM(CASE WHEN a.hipdata_code IN ('NRD','NRH','FWF') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_fss,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.income ELSE 0 END) AS inc_stp,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.inc03 ELSE 0 END) AS inc_lab_stp,
	SUM(CASE WHEN a.hipdata_code IN ('STP') AND a.paidst NOT IN ('01','03') THEN a.inc12 ELSE 0 END) AS inc_drug_stp,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.income ELSE 0 END) AS inc_pay,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.inc03 ELSE 0 END) AS inc_lab_pay,
	SUM(CASE WHEN (a.hipdata_code IN ('A1','A9') OR a.paidst IN ('01','03')) THEN a.inc12 ELSE 0 END) AS inc_drug_pay
FROM (
	SELECT 
		ov.vstdate, ov.vn, ov.hn, v.cid,
		IF(v.pdx IN ({{PP_ICD10_LIST}}) OR v.pdx LIKE 'Z%', 'PP', 'OP') AS diagtype,
		p.hipdata_code, p.paidst,
		COALESCE(v.income, 0) AS income,
		COALESCE(v.inc03, 0) AS inc03,
		COALESCE(v.inc12, 0) AS inc12,
		IF(vp.is_ep = 'Y', 'Y', '') AS endpoint,
		COALESCE(vp.is_incup, '') AS incup,
		COALESCE(vp.is_inprov, '') AS inprov,
		COALESCE(vp.is_outprov, '') AS outprov,
		IF(dt.vn IS NOT NULL, 'Y', '') AS dent,
		IF(pl.vn IS NOT NULL, 'Y', '') AS physic,
		IF(hm.vn IS NOT NULL, 'Y', '') AS healthmed,
		IF(anc.vn IS NOT NULL, 'Y', '') AS anc,
		IF(oi.ovstist = '08', 'Y', '') AS telehealth,
		IF(ma.cid IS NOT NULL, 'Y', '') AS moph_oapp,
		COALESCE(r_out.referout_inprov, '') AS referout_inprov,
		COALESCE(r_out.referout_outprov, '') AS referout_outprov,
		COALESCE(r_out_ipd.referout_inprov_ipd, '') AS referout_inprov_ipd,
		COALESCE(r_out_ipd.referout_outprov_ipd, '') AS referout_outprov_ipd,
		IF(r_in.inprov = 'Y' AND ip.vn IS NULL, 'Y', '') AS referin_inprov,
		IF(r_in.outprov = 'Y' AND ip.vn IS NULL, 'Y', '') AS referin_outprov,
		IF(r_in.inprov = 'Y' AND ip.vn IS NOT NULL, 'Y', '') AS referin_inprov_ipd,
		IF(r_in.outprov = 'Y' AND ip.vn IS NOT NULL, 'Y', '') AS referin_outprov_ipd
	FROM ovst ov
	LEFT JOIN vn_stat v ON v.vn = ov.vn
	LEFT JOIN ipt ip ON ip.vn = ov.vn
	LEFT JOIN pttype p ON p.pttype = ov.pttype
	LEFT JOIN ovstist oi ON oi.ovstist = ov.ovstist
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN hospmain = '{{HOSPCODE}}' THEN 'Y' END) AS is_incup,
			MAX(CASE WHEN hospmain != '{{HOSPCODE}}' AND hospmain IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS is_inprov,
			MAX(CASE WHEN hospmain NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS is_outprov,
			MAX(CASE WHEN auth_code LIKE 'EP%' THEN 'Y' END) AS is_ep
		FROM visit_pttype
		GROUP BY vn
	) vp ON vp.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM dtmain) dt ON dt.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM physic_list) pl ON pl.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM health_med_service) hm ON hm.vn = ov.vn
	LEFT JOIN (SELECT DISTINCT vn FROM person_anc_service) anc ON anc.vn = ov.vn
	LEFT JOIN (
		SELECT DISTINCT cid, appointment_date
		FROM moph_appointment_list
	) ma ON ma.cid = v.cid AND ma.appointment_date = ov.vstdate
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_inprov,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_outprov
		FROM referout
		GROUP BY vn
	) r_out ON r_out.vn = ov.vn
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_inprov_ipd,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS referout_outprov_ipd
		FROM referout
		GROUP BY vn
	) r_out_ipd ON r_out_ipd.vn = ip.an
	LEFT JOIN (
		SELECT vn,
			MAX(CASE WHEN refer_hospcode IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS inprov,
			MAX(CASE WHEN refer_hospcode NOT IN ({{PROVINCE_HOSPCODES}}) THEN 'Y' END) AS outprov
		FROM referin
		GROUP BY vn
	) r_in ON r_in.vn = ov.vn
	WHERE ov.vstdate BETWEEN ? AND ?
) a
LEFT JOIN (
	SELECT DATE(reply_date_time) as d,
		COUNT(DISTINCT CASE WHEN lh.chwpart = (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_inprov,
		COUNT(DISTINCT CASE WHEN lh.chwpart != (SELECT chwpart FROM opdconfig LIMIT 1) THEN vn END) as visit_referback_outprov
	FROM referin_reply rr
	LEFT JOIN hospcode lh ON lh.hospcode = rr.reply_hospcode
	WHERE DATE(reply_date_time) BETWEEN ? AND ?
	GROUP BY DATE(reply_date_time)
) rb ON rb.d = a.vstdate
LEFT JOIN (
	SELECT o.operation_request_date as d, COUNT(DISTINCT o.operation_id) as visit_operation
	FROM operation_list o
	WHERE o.operation_request_date BETWEEN ? AND ?
	GROUP BY o.operation_request_date
) op ON op.d = a.vstdate
LEFT JOIN (
	SELECT appointment_date as d, COUNT(DISTINCT cid) as cnt
	FROM moph_appointment_list
	WHERE appointment_date BETWEEN ? AND ?
	GROUP BY appointment_date
) ma_booking ON ma_booking.d = a.vstdate
GROUP BY a.vstdate
ORDER BY a.vstdate
SQL,

            'ipd' => <<<'SQL'
SELECT 
	dchdate,
	COUNT(DISTINCT an) AS an_total,
	COALESCE(SUM(admdate), 0) AS admdate,
	ROUND((COALESCE(SUM(a.admdate), 0) * 100) / (? * CASE 
		WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) THEN DAY(CURDATE()) 
		ELSE DAY(LAST_DAY(a.dchdate)) 
	END), 2) AS bed_occupancy,
	ROUND((COALESCE(SUM(a.admdate), 0) / CASE 
		WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) THEN DAY(CURDATE()) 
		ELSE DAY(LAST_DAY(a.dchdate)) 
	END), 2) AS active_bed,
	ROUND(COALESCE(SUM(adjrw), 0) / COUNT(DISTINCT an), 2) AS cmi,
	ROUND(COALESCE(SUM(adjrw), 0), 5) AS adjrw,
	COALESCE(SUM(income), 0) AS inc_total,
	COALESCE(SUM(inc03), 0) AS inc_lab_total,
	COALESCE(SUM(inc12), 0) AS inc_drug_total
FROM (
	SELECT a.dchdate, a.an, a.admdate, i.adjrw, a.income, a.inc03, a.inc12
	FROM ipt i
	LEFT JOIN an_stat a ON a.an = i.an
	LEFT JOIN pttype p ON p.pttype = a.pttype
	WHERE a.dchdate BETWEEN ? AND ?
	  AND a.pdx NOT IN ('Z290', 'Z208')
	GROUP BY a.an
) AS a
GROUP BY dchdate
ORDER BY dchdate
SQL,

            'bed_total' => <<<'SQL'
SELECT 
	COALESCE((SELECT SUM(bed_count) FROM ward WHERE ward_active = 'Y'), ?) AS bed_qty,
	COALESCE(COUNT(DISTINCT b.bedno), 0) AS bed_use
FROM ipt i 
INNER JOIN iptadm ia ON ia.an = i.an
LEFT JOIN bedno b ON b.bedno = ia.bedno
WHERE i.confirm_discharge = 'N' 
  AND (b.export_code IS NOT NULL AND b.export_code <> '')
SQL,

            'bed_dep' => <<<'SQL'
SELECT 
	IFNULL(b.export_code, 0) AS bed_code,
	IFNULL(COUNT(DISTINCT b.bedno), 0) AS bed_qty,
	IFNULL(b1.bed_use, 0) AS bed_use
FROM bedno b
LEFT JOIN (
	SELECT b.export_code, COUNT(DISTINCT b.bedno) AS bed_use
	FROM ipt i
	INNER JOIN iptadm ia ON ia.an = i.an
	LEFT JOIN bedno b ON b.bedno = ia.bedno
	WHERE b.export_code IS NOT NULL 
	  AND b.export_code <> ''
	  AND i.confirm_discharge = 'N'
	GROUP BY b.export_code
) b1 ON b1.export_code = b.export_code
WHERE b.export_code IS NOT NULL 
  AND b.export_code <> ''
GROUP BY b.export_code 
ORDER BY b.export_code
SQL,
        ];
    }

    /**
     * Dispatch remote sync task to one or all hospital agents.
     */
    public function dispatchRemoteSync(Request $request)
    {
        $request->validate([
            'target'     => 'required|string', // 'all' or specific hcode
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d',
        ]);

        $target = $request->input('target');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $taskId = 'task_' . time() . '_' . substr(md5(uniqid()), 0, 6);

        $taskPayload = [
            'task_id'    => $taskId,
            'action'     => 'sync_range',
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'created_at' => now()->toDateTimeString(),
        ];

        $dispatchedHospcodes = [];
        if ($target === 'all') {
            $hospcodes = Hospital::pluck('hospcode')->filter()->toArray();
            foreach ($hospcodes as $hcode) {
                Cache::put("agent_remote_task_{$hcode}", $taskPayload, 3600);
                $dispatchedHospcodes[] = $hcode;
            }
        } else {
            Cache::put("agent_remote_task_{$target}", $taskPayload, 3600);
            $dispatchedHospcodes[] = $target;
        }

        return response()->json([
            'status'     => 'success',
            'task_id'    => $taskId,
            'message'    => 'ส่งคำสั่งดึงข้อมูลย้อนหลังไปยัง ' . count($dispatchedHospcodes) . ' โรงพยาบาลเรียบร้อยแล้ว Agent จะเริ่มทำงานในรอบถัดไปทันที',
            'hospcodes'  => $dispatchedHospcodes,
        ]);
    }

    /**
     * Issue/Reset Sanctum token for hospital.
     */
    public function issueToken(Request $request, $hcode)
    {
        $hospital = Hospital::where('hospcode', $hcode)->firstOrFail();
        
        // Delete old tokens
        $hospital->tokens()->delete();

        // Create new token
        $token = $hospital->createToken("{$hcode}-ingest", ['ingest'])->plainTextToken;

        // Save into hospital record for persistent copy/view
        $hospital->update(['token_api' => $token]);

        return response()->json([
            'status'  => 'success',
            'message' => 'สร้าง Token ใหม่สำหรับ รพ. ' . ($hospital->name ?? $hcode) . ' เรียบร้อยแล้ว',
            'token'   => $token,
        ]);
    }

    /**
     * Download pre-configured config.yaml for specific hospital.
     */
    public function downloadConfig($hcode)
    {
        $hospital = Hospital::where('hospcode', $hcode)->firstOrFail();
        $tokenStr = $hospital->token_api;
        
        if (empty($tokenStr)) {
            $token = $hospital->createToken("{$hcode}-ingest", ['ingest'])->plainTextToken;
            $hospital->update(['token_api' => $token]);
            $tokenStr = $token;
        }

        $serverUrl = url('/');

        $yamlContent = <<<YAML
hospital:
  code: "{$hcode}"
  name: "{$hospital->name}"
  token: "{$tokenStr}"
  server_url: "{$serverUrl}"
  bed_qty: 30

database:
  driver: "mysql"
  host: "127.0.0.1"
  port: 3306
  username: "rims"
  password: "your_password"
  database: "hosxp"

schedule:
  opd_cron: "0 */2 * * *"
  ipd_cron: "0 */2 * * *"
  bed_cron: "*/15 * * * *"
  sync_days_back: 10
  chunk_size: 200

web:
  port: 8989
  open_browser: true
YAML;

        return response($yamlContent, 200, [
            'Content-Type'        => 'application/x-yaml',
            'Content-Disposition' => "attachment; filename=\"config-{$hcode}.yaml\"",
        ]);
    }

    /**
     * Dispatch remote auto-update task to one or all hospital agents.
     */
    public function dispatchRemoteUpdate(Request $request)
    {
        $request->validate([
            'target' => 'required|string', // 'all' or specific hcode
        ]);

        $target = $request->input('target');
        $taskId = 'update_task_' . time() . '_' . substr(md5(uniqid()), 0, 6);
        $downloadUrl = url('/api/agent/download-latest');

        $taskPayload = [
            'task_id'      => $taskId,
            'action'       => 'update_client',
            'download_url' => $downloadUrl,
            'version'      => '1.0.1',
            'created_at'   => now()->toDateTimeString(),
        ];

        $dispatchedHospcodes = [];
        if ($target === 'all') {
            $hospcodes = Hospital::pluck('hospcode')->filter()->toArray();
            foreach ($hospcodes as $hcode) {
                Cache::put("agent_remote_task_{$hcode}", $taskPayload, 3600);
                $dispatchedHospcodes[] = $hcode;
            }
        } else {
            Cache::put("agent_remote_task_{$target}", $taskPayload, 3600);
            $dispatchedHospcodes[] = $target;
        }

        return response()->json([
            'status'     => 'success',
            'task_id'    => $taskId,
            'message'    => 'ส่งคำสั่งอัปเดต Agent ไปยัง ' . count($dispatchedHospcodes) . ' โรงพยาบาลเรียบร้อยแล้ว Agent ที่ออนไลน์อยู่จะดาวน์โหลดเวอร์ชั่นใหม่และรีสตาร์ทอัตโนมัติภายใน 1 นาที',
            'hospcodes'  => $dispatchedHospcodes,
        ]);
    }

    /**
     * Download AOPOD-Agent.exe binary.
     */
    public function downloadExe()
    {
        $exePath = base_path('agent/AOPOD-Agent.exe');
        if (!file_exists($exePath)) {
            $exePath = base_path('agent/aopod-agent.exe');
        }

        if (!file_exists($exePath)) {
            return back()->with('error', 'ไม่พบไฟล์ AOPOD-Agent.exe บนเซิร์ฟเวอร์ กรุณาทำการคอมไพล์ก่อน');
        }

        return response()->download($exePath, 'AOPOD-Agent.exe', [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
