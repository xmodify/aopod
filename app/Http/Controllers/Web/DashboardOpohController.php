<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\MainSetting;
use App\Models\OpohDoctor;
use App\Models\OpohOpdService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardOpohController extends Controller
{
    /**
     * โหลดรายชื่อ รพช. และ รพ.แม่ข่าย แบบ dynamic รองรับทั้งอำนาจเจริญและจังหวัดอื่น
     */
    private function resolveOpohHospitals(): array
    {
        $hubCode = trim((string) MainSetting::get('opoh_hub_hospcode', '10703')) ?: '10703';
        $excludeRaw = (string) MainSetting::get('opoh_exclude_hospcodes', '00025');
        $excludeCodes = array_values(array_filter(array_map('trim', explode(',', $excludeRaw))));

        $preferredOrder = [
            '10985' => 1,
            '10986' => 2,
            '10987' => 3,
            '10988' => 4,
            '10989' => 5,
            '10990' => 6,
        ];

        $allHosp = Hospital::where('is_active', 1)->get(['hospcode', 'name']);

        $formatShort = function (string $name, string $code): string {
            $short = str_replace('โรงพยาบาล', 'รพ.', trim($name));
            return $short !== '' ? $short : $code;
        };

        $hubName = $hubCode === '10703' ? 'รพ.อำนาจเจริญ' : $hubCode;
        $communityRows = [];
        foreach ($allHosp as $h) {
            $code = trim((string) $h->hospcode);
            if ($code === '') {
                continue;
            }
            if ($code === $hubCode) {
                $hubName = $formatShort((string) $h->name, $code);
                continue;
            }
            if (in_array($code, $excludeCodes, true)) {
                continue;
            }
            $communityRows[] = [
                'hospcode' => $code,
                'name'     => $formatShort((string) $h->name, $code),
                'order'    => $preferredOrder[$code] ?? (1000 + (int) $code),
            ];
        }

        if (empty($communityRows)) {
            $communityRows = [
                ['hospcode' => '10985', 'name' => 'รพ.ชานุมาน', 'order' => 1],
                ['hospcode' => '10986', 'name' => 'รพ.ปทุมราชวงศา', 'order' => 2],
                ['hospcode' => '10987', 'name' => 'รพ.พนา', 'order' => 3],
                ['hospcode' => '10988', 'name' => 'รพ.เสนางคนิคม', 'order' => 4],
                ['hospcode' => '10989', 'name' => 'รพ.หัวตะพาน', 'order' => 5],
                ['hospcode' => '10990', 'name' => 'รพ.ลืออำนาจ', 'order' => 6],
            ];
        }

        usort($communityRows, fn ($a, $b) => $a['order'] <=> $b['order'] ?: strcmp($a['hospcode'], $b['hospcode']));

        $community = [];
        $modalHospitals = [];
        foreach ($communityRows as $r) {
            $community[$r['hospcode']] = $r['name'];
            $modalHospitals[$r['hospcode']] = ['name' => $r['name'], 'type' => 'รพช.'];
        }
        $modalHospitals[$hubCode] = ['name' => $hubName, 'type' => 'รพ.แม่ข่าย'];

        $selectableOrigins = [$hubCode => $hubName . ' (รพ.แม่ข่าย)'];
        foreach ($community as $cCode => $cName) {
            $selectableOrigins[$cCode] = $cName;
        }

        return [
            'community'          => $community,
            'community_codes'    => array_map('strval', array_column($communityRows, 'hospcode')),
            'hub_code'           => (string) $hubCode,
            'hub_name'           => $hubName,
            'modal_hospitals'    => $modalHospitals,
            'selectable_origins' => $selectableOrigins,
        ];
    }

    /**
     * กรองไม่นับรายการที่แพทย์ตรวจใน รพ.ต้นสังกัดของตนเอง (ยกเว้นกรณีอนุญาต $allowHubCode สำหรับคอลัมน์อ้างอิงแม่ข่าย)
     */
    private function applyOriginExclusion($query, string $alias, string $hospCol = 'hospcode', string $docCol = 'doctor_id', ?string $allowHubCode = null)
    {
        return $query->leftJoin('opoh_doctors as _od_org', '_od_org.id', '=', "{$alias}.{$docCol}")
            ->where(function ($q) use ($alias, $hospCol, $allowHubCode) {
                if ($allowHubCode !== null) {
                    $q->where("{$alias}.{$hospCol}", $allowHubCode)
                      ->orWhereNull('_od_org.origin_hospcode')
                      ->orWhere('_od_org.origin_hospcode', '')
                      ->orWhereColumn('_od_org.origin_hospcode', '!=', "{$alias}.{$hospCol}");
                } else {
                    $q->whereNull('_od_org.origin_hospcode')
                      ->orWhere('_od_org.origin_hospcode', '')
                      ->orWhereColumn('_od_org.origin_hospcode', '!=', "{$alias}.{$hospCol}");
                }
            });
    }

    /**
     * Query สรุป IPD แบบไม่นับซ้ำราย AN (ใช้ opoh_ipd_hospital_summaries และ fallback ไป opoh_ipd_service สำหรับวันที่ยังไม่มี summary)
     */
    private function ipdHospitalRowsQuery(array $hospcodes, string $startDate, string $endDate, ?string $allowHubCode = null)
    {
        $cols = "hospcode, dchdate, an_total, admdate_total, adjrw_total, operation_total,
            an_ucs_incup, an_ucs_outprov, an_ofc, an_lgo, an_sss, an_other,
            admdate_ucs_incup, admdate_ucs_outprov, admdate_ofc, admdate_lgo, admdate_sss, admdate_other,
            adjrw_ucs_incup, adjrw_ucs_outprov, adjrw_ofc, adjrw_lgo, adjrw_sss, adjrw_other";

        $sumQ = DB::table('opoh_ipd_hospital_summaries')
            ->whereIn('hospcode', $hospcodes)
            ->where('dchdate', '>=', $startDate)
            ->where('dchdate', '<=', $endDate)
            ->selectRaw($cols);

        $fallbackCols = "s.hospcode, s.dchdate, s.an_total, s.admdate_total, s.adjrw_total, s.operation_total,
            s.an_ucs_incup, s.an_ucs_outprov, s.an_ofc, s.an_lgo, s.an_sss, s.an_other,
            s.admdate_ucs_incup, s.admdate_ucs_outprov, s.admdate_ofc, s.admdate_lgo, s.admdate_sss, s.admdate_other,
            s.adjrw_ucs_incup, s.adjrw_ucs_outprov, s.adjrw_ofc, s.adjrw_lgo, s.adjrw_sss, s.adjrw_other";

        $fallbackQ = DB::table('opoh_ipd_service as s')
            ->whereIn('s.hospcode', $hospcodes)
            ->where('s.dchdate', '>=', $startDate)
            ->where('s.dchdate', '<=', $endDate)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('opoh_ipd_hospital_summaries as hs')
                  ->whereColumn('hs.hospcode', 's.hospcode')
                  ->whereColumn('hs.dchdate', 's.dchdate');
            })
            ->selectRaw($fallbackCols);
        $this->applyOriginExclusion($fallbackQ, 's', 'hospcode', 'doctor_id', $allowHubCode);

        return DB::query()->fromSub($sumQ->unionAll($fallbackQ), 'ipd_dedup');
    }

    /**
     * Query ค่ารักษา 16 หมวด (OPD/IPD/ALL) โดย IPD ใช้ยอดไม่นับซ้ำราย AN และกรองเคสตรวจที่ รพ.ต้นสังกัดออก
     */
    private function incomeCategoriesQuery(string $serviceType, array $hospcodes, string $startDate, string $endDate, ?string $allowHubCode = null)
    {
        $opdQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'OPD')
            ->whereIn('ic.hospcode', $hospcodes)
            ->where('ic.vstdate', '>=', $startDate)
            ->where('ic.vstdate', '<=', $endDate)
            ->selectRaw("ic.hospcode, ic.vstdate, ic.drg_chrgitem_id, ic.drg_chrgitem_name,
                ic.inc_ucs_incup, ic.inc_ucs_outprov, ic.inc_ofc, ic.inc_lgo, ic.inc_sss, ic.inc_other, ic.inc_total");
        $this->applyOriginExclusion($opdQ, 'ic', 'hospcode', 'doctor_id', $allowHubCode);

        if ($serviceType === 'OPD') {
            return DB::query()->fromSub($opdQ, 'inc_dedup');
        }

        $ipdSumQ = DB::table('opoh_ipd_income_summaries')
            ->whereIn('hospcode', $hospcodes)
            ->where('dchdate', '>=', $startDate)
            ->where('dchdate', '<=', $endDate)
            ->selectRaw("hospcode, dchdate as vstdate, drg_chrgitem_id, drg_chrgitem_name,
                inc_ucs_incup, inc_ucs_outprov, inc_ofc, inc_lgo, inc_sss, inc_other, inc_total");

        $ipdFallbackQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'IPD')
            ->whereIn('ic.hospcode', $hospcodes)
            ->where('ic.vstdate', '>=', $startDate)
            ->where('ic.vstdate', '<=', $endDate)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('opoh_ipd_income_summaries as ins')
                  ->whereColumn('ins.hospcode', 'ic.hospcode')
                  ->whereColumn('ins.dchdate', 'ic.vstdate');
            })
            ->selectRaw("ic.hospcode, ic.vstdate, ic.drg_chrgitem_id, ic.drg_chrgitem_name,
                ic.inc_ucs_incup, ic.inc_ucs_outprov, ic.inc_ofc, ic.inc_lgo, ic.inc_sss, ic.inc_other, ic.inc_total");
        $this->applyOriginExclusion($ipdFallbackQ, 'ic', 'hospcode', 'doctor_id', $allowHubCode);

        $ipdUnion = $ipdSumQ->unionAll($ipdFallbackQ);
        if ($serviceType === 'IPD') {
            return DB::query()->fromSub($ipdUnion, 'inc_dedup');
        }

        return DB::query()->fromSub($opdQ->unionAll($ipdUnion), 'inc_dedup');
    }

    /**
     * Display OPOH / AOPH Service Single-Page Dashboard
     */
    public function index(Request $request)
    {
        // 1. Fiscal Year Calculation
        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(5)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        $year_info = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        $fiscal_start = $year_info->DATE_BEGIN ?? Carbon::now()->startOfYear()->toDateString();
        $fiscal_end   = $year_info->DATE_END ?? Carbon::now()->endOfYear()->toDateString();

        // 2. Date Filtering (Default to current week within the fiscal year)
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek   = Carbon::now()->endOfWeek()->toDateString();

        $filter_type = $request->input('filter_type', 'week'); // 'week', 'month', 'custom', 'fiscal'
        if ($filter_type === 'fiscal') {
            $current_start = $fiscal_start;
            $current_end   = $fiscal_end;
        } elseif ($filter_type === 'month') {
            $current_start = Carbon::now()->startOfMonth()->toDateString();
            $current_end   = Carbon::now()->endOfMonth()->toDateString();
        } elseif ($filter_type === 'custom') {
            $current_start = $request->input('start_date', $startOfWeek);
            $current_end   = $request->input('end_date', $endOfWeek);
        } else {
            // Default current week
            $current_start = $request->input('start_date', $startOfWeek);
            $current_end   = $request->input('end_date', $endOfWeek);
        }

        // 3. Target Community Hospitals & Hub Hospital (โหลดแบบ dynamic รองรับทุกจังหวัด)
        $hospitalsMeta = $this->resolveOpohHospitals();
        $hospitalsList = $hospitalsMeta['community'];
        $communityCodes = $hospitalsMeta['community_codes'];
        $hubHospitalCode = $hospitalsMeta['hub_code'];
        $hubHospitalName = $hospitalsMeta['hub_name'];
        $allModalHospitals = $hospitalsMeta['modal_hospitals'];
        $allHospCodes = array_keys($allModalHospitals);

        // 4. Main Specialties (7 สาขาหลัก)
        $specialties = [
            '01' => ['name' => 'อายุรกรรม', 'color' => '#0d6efd', 'icon' => 'fa-stethoscope'],
            '02' => ['name' => 'ศัลยกรรมทั่วไป', 'color' => '#6f42c1', 'icon' => 'fa-syringe'],
            '06' => ['name' => 'ศัลยกรรมกระดูก', 'color' => '#d63384', 'icon' => 'fa-bone'],
            '03' => ['name' => 'สูตินรีเวชกรรม', 'color' => '#fd7e14', 'icon' => 'fa-person-pregnant'],
            '05' => ['name' => 'กุมารเวชกรรม', 'color' => '#198754', 'icon' => 'fa-baby'],
            '07' => ['name' => 'จิตเวช', 'color' => '#20c997', 'icon' => 'fa-brain'],
            '08' => ['name' => 'จักษุ', 'color' => '#0dcaf0', 'icon' => 'fa-eye'],
        ];

        // 5. Query Aggregations
        // ยอดสะสม fix เริ่มต้นจาก ต.ค. 68 (2025-10-01) ถึงวันปัจจุบัน ($cumulative_end)
        $cumulative_start = '2025-10-01';
        $cumulative_end   = Carbon::now()->toDateString(); // ถึงปัจจุบัน

        // 5.1 Cumulative total cards by specialty (รพช. ไม่นับแพทย์ตรวจที่ รพ.ต้นสังกัดของตนเอง)
        $specialtyStatsQ = DB::table('opoh_opd_service as s')
            ->where('s.vstdate', '>=', $cumulative_start)
            ->where('s.vstdate', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($specialtyStatsQ, 's');
        $specialtyStats = $specialtyStatsQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.visit_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // 5.2 Current period visits per specialty ($current_start ถึง $current_end) เฉพาะ รพช.
        $specialtyPeriodVisitsQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($specialtyPeriodVisitsQ, 's');
        $specialtyPeriodVisits = $specialtyPeriodVisitsQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.visit_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // 5.3 Current period by specialty and hospcode (รพช. + รพ.แม่ข่าย สำหรับคอลัมน์อ้างอิง)
        $specHospMatrixQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $allHospCodes);
        $this->applyOriginExclusion($specHospMatrixQ, 's', 'hospcode', 'doctor_id', $hubHospitalCode);
        $specHospMatrixRaw = $specHospMatrixQ
            ->groupBy('s.spclty_code', 's.hospcode')
            ->selectRaw('s.spclty_code, s.hospcode, COALESCE(SUM(s.visit_total), 0) as visits')
            ->get();

        $specHospMatrix = [];
        foreach ($specHospMatrixRaw as $m) {
            $specHospMatrix[$m->spclty_code][$m->hospcode] = (int)$m->visits;
        }

        $specialtyPeriodIncQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'OPD')
            ->whereBetween('ic.vstdate', [$current_start, $current_end])
            ->whereIn('ic.hospcode', $communityCodes);
        $this->applyOriginExclusion($specialtyPeriodIncQ, 'ic');
        $specialtyPeriodInc = $specialtyPeriodIncQ
            ->groupBy('ic.spclty_code')
            ->selectRaw('ic.spclty_code as k, COALESCE(SUM(ic.inc_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // Build Specialty Rows for OPD Table (7 สาขาเป็นแถว และ รพ.เป็นคอลัมน์)
        $specialtyRows = [];
        $specColTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'inc'        => 0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $specColTotals[$hcode] = 0;
        }

        foreach ($specialties as $code => $spec) {
            $cumul = (int)($specialtyStats[$code] ?? 0);
            $period = (int)($specialtyPeriodVisits[$code] ?? 0);
            $inc = (float)($specialtyPeriodInc[$code] ?? 0);
            $amnatVisits = (int)($specHospMatrix[$code][$hubHospitalCode] ?? 0);

            $specColTotals['cumulative'] += $cumul;
            $specColTotals['period'] += $period;
            $specColTotals['inc'] += $inc;
            $specColTotals['amnat'] += $amnatVisits;

            $hospVisits = [];
            foreach ($hospitalsList as $hcode => $hname) {
                $cnt = (int)($specHospMatrix[$code][$hcode] ?? 0);
                $hospVisits[$hcode] = $cnt;
                $specColTotals[$hcode] += $cnt;
            }

            $specialtyRows[] = [
                'code'         => $code,
                'name'         => $spec['name'],
                'color'        => $spec['color'],
                'icon'         => $spec['icon'],
                'cumulative'   => $cumul,
                'period'       => $period,
                'inc'          => $inc,
                'hosp_visits'  => $hospVisits,
                'amnat_visits' => $amnatVisits,
            ];
        }

        $sumPast = max(0, $specColTotals['cumulative'] - $specColTotals['period']);
        $sumCurrent = $specColTotals['period'];
        $sumCumulative = $specColTotals['cumulative'];

        // 5.4 Financial & Scheme Totals (Current Period & Cumulative เฉพาะ รพช. ไม่นับเคสตรวจที่ รพ.ต้นสังกัด)
        $summaryQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($summaryQ, 's');
        $summary = $summaryQ->selectRaw("
                COALESCE(SUM(s.visit_total), 0)     AS visit_total,
                COALESCE(SUM(s.hn_total), 0)        AS hn_total,
                COALESCE(SUM(s.visit_ucs), 0)       AS visit_ucs,
                COALESCE(SUM(s.visit_ofc), 0)       AS visit_ofc,
                COALESCE(SUM(s.visit_lgo), 0)       AS visit_lgo,
                COALESCE(SUM(s.visit_sss), 0)       AS visit_sss,
                COALESCE(SUM(s.visit_other), 0)     AS visit_other,
                COALESCE(SUM(s.visit_telemed), 0)   AS visit_telemed,
                COALESCE(SUM(s.refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(s.admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

        // Income breakdown for OPD Current Period
        $opdPeriodIncSum = $this->incomeCategoriesQuery('OPD', $communityCodes, $current_start, $current_end)
            ->selectRaw("
                COALESCE(SUM(inc_total), 0) AS inc_total,
                COALESCE(SUM(CASE WHEN drg_chrgitem_id IN (3, 4, 17) THEN inc_total ELSE 0 END), 0) AS inc_drug_total,
                COALESCE(SUM(CASE WHEN drg_chrgitem_id = 7 THEN inc_total ELSE 0 END), 0) AS inc_lab_total
            ")
            ->first();

        if ($summary) {
            $summary->inc_total      = (float)($opdPeriodIncSum->inc_total ?? 0.0);
            $summary->inc_drug_total = (float)($opdPeriodIncSum->inc_drug_total ?? 0.0);
            $summary->inc_lab_total  = (float)($opdPeriodIncSum->inc_lab_total ?? 0.0);
        }

        $sumTelemed = (int)($summary->visit_telemed ?? 0);
        $sumOnsite  = max(0, $sumCurrent - $sumTelemed);

        $fiscalSummaryQ = DB::table('opoh_opd_service as s')
            ->where('s.vstdate', '>=', $cumulative_start)
            ->where('s.vstdate', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($fiscalSummaryQ, 's');
        $fiscalSummary = $fiscalSummaryQ->selectRaw("
                COALESCE(SUM(s.visit_total), 0)     AS visit_total,
                COALESCE(SUM(s.visit_telemed), 0)   AS visit_telemed,
                COALESCE(SUM(s.refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(s.admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

        $opdCumulativeIncSum = $this->incomeCategoriesQuery('OPD', $communityCodes, $cumulative_start, $cumulative_end)
            ->selectRaw("
                COALESCE(SUM(inc_total), 0) AS inc_total,
                COALESCE(SUM(CASE WHEN drg_chrgitem_id IN (3, 4, 17) THEN inc_total ELSE 0 END), 0) AS inc_drug_total,
                COALESCE(SUM(CASE WHEN drg_chrgitem_id = 7 THEN inc_total ELSE 0 END), 0) AS inc_lab_total
            ")
            ->first();

        if ($fiscalSummary) {
            $fiscalSummary->inc_total      = (float)($opdCumulativeIncSum->inc_total ?? 0.0);
            $fiscalSummary->inc_drug_total = (float)($opdCumulativeIncSum->inc_drug_total ?? 0.0);
            $fiscalSummary->inc_lab_total  = (float)($opdCumulativeIncSum->inc_lab_total ?? 0.0);
        }

        // 5.5 IPD Aggregations (ผู้ป่วยใน - 7 สาขาเป็นแถว และ รพ.เป็นคอลัมน์)
        $ipdSpecialtyStatsQ = DB::table('opoh_ipd_service as s')
            ->where('s.dchdate', '>=', $cumulative_start)
            ->where('s.dchdate', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($ipdSpecialtyStatsQ, 's');
        $ipdSpecialtyStats = $ipdSpecialtyStatsQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.an_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $ipdPeriodStatsQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($ipdPeriodStatsQ, 's');
        $ipdPeriodStats = $ipdPeriodStatsQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.an_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $ipdPeriodLosQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($ipdPeriodLosQ, 's');
        $ipdPeriodLos = $ipdPeriodLosQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.admdate_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $ipdPeriodRwQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($ipdPeriodRwQ, 's');
        $ipdPeriodRw = $ipdPeriodRwQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.adjrw_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $ipdPeriodIncQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'IPD')
            ->whereBetween('ic.vstdate', [$current_start, $current_end])
            ->whereIn('ic.hospcode', $communityCodes);
        $this->applyOriginExclusion($ipdPeriodIncQ, 'ic');
        $ipdPeriodInc = $ipdPeriodIncQ
            ->groupBy('ic.spclty_code')
            ->selectRaw('ic.spclty_code as k, COALESCE(SUM(ic.inc_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $ipdSpecHospMatrixQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $allHospCodes);
        $this->applyOriginExclusion($ipdSpecHospMatrixQ, 's', 'hospcode', 'doctor_id', $hubHospitalCode);
        $ipdSpecHospMatrixRaw = $ipdSpecHospMatrixQ
            ->groupBy('s.spclty_code', 's.hospcode')
            ->selectRaw('s.spclty_code, s.hospcode, COALESCE(SUM(s.an_total), 0) as ans')
            ->get();

        $ipdSpecHospMatrix = [];
        foreach ($ipdSpecHospMatrixRaw as $m) {
            $ipdSpecHospMatrix[$m->spclty_code][$m->hospcode] = (int)$m->ans;
        }

        $ipdSpecialtyRows = [];
        $ipdSpecColTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'los'        => 0,
            'rw'         => 0.0,
            'inc'        => 0.0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $ipdSpecColTotals[$hcode] = 0;
        }

        foreach ($specialties as $code => $spec) {
            $cumul = (int)($ipdSpecialtyStats[$code] ?? 0);
            $period = (int)($ipdPeriodStats[$code] ?? 0);
            $los = (int)($ipdPeriodLos[$code] ?? 0);
            $rw = (float)($ipdPeriodRw[$code] ?? 0.0);
            $inc = (float)($ipdPeriodInc[$code] ?? 0.0);
            $amnatAns = (int)($ipdSpecHospMatrix[$code][$hubHospitalCode] ?? 0);

            $ipdSpecColTotals['cumulative'] += $cumul;
            $ipdSpecColTotals['period'] += $period;
            $ipdSpecColTotals['los'] += $los;
            $ipdSpecColTotals['rw'] += $rw;
            $ipdSpecColTotals['inc'] += $inc;
            $ipdSpecColTotals['amnat'] += $amnatAns;

            $hospAns = [];
            foreach ($hospitalsList as $hcode => $hname) {
                $cnt = (int)($ipdSpecHospMatrix[$code][$hcode] ?? 0);
                $hospAns[$hcode] = $cnt;
                $ipdSpecColTotals[$hcode] += $cnt;
            }

            $ipdSpecialtyRows[] = [
                'code'        => $code,
                'name'        => $spec['name'],
                'color'       => $spec['color'],
                'icon'        => $spec['icon'],
                'cumulative'  => $cumul,
                'period'      => $period,
                'los'         => $los,
                'rw'          => $rw,
                'inc'         => $inc,
                'hosp_ans'    => $hospAns,
                'amnat_ans'   => $amnatAns,
            ];
        }

        // ยอดรวม IPD ระดับ รพ. (ไม่นับซ้ำราย AN และกรองเคสที่แพทย์ตรวจ ณ รพ.ต้นสังกัดออก)
        $ipdCumHospitalRow = $this->ipdHospitalRowsQuery($communityCodes, $cumulative_start, $cumulative_end)
            ->selectRaw('COALESCE(SUM(an_total), 0) AS an_total')
            ->first();
        $ipdPeriodHospitalRow = $this->ipdHospitalRowsQuery($communityCodes, $current_start, $current_end)
            ->selectRaw('COALESCE(SUM(an_total), 0) AS an_total, COALESCE(SUM(admdate_total), 0) AS los_total, COALESCE(SUM(adjrw_total), 0) AS adjrw_total')
            ->first();
        $ipdPeriodHospitalIncRow = $this->incomeCategoriesQuery('IPD', $communityCodes, $current_start, $current_end)
            ->selectRaw('COALESCE(SUM(inc_total), 0) AS inc_total')
            ->first();

        $ipdSumCumulative = (int)($ipdCumHospitalRow->an_total ?? 0);
        $ipdSumCurrent    = (int)($ipdPeriodHospitalRow->an_total ?? 0);
        $ipdSumPast       = max(0, $ipdSumCumulative - $ipdSumCurrent);
        $ipdSumLos        = (int)($ipdPeriodHospitalRow->los_total ?? 0);
        $ipdSumRw         = (float)($ipdPeriodHospitalRow->adjrw_total ?? 0.0);
        $ipdSumInc        = (float)($ipdPeriodHospitalIncRow->inc_total ?? 0.0);

        // 5.6 กราฟแท่งแนวโน้ม 12 เดือนของปีงบประมาณ (Monthly Trend Chart)
        $hospColors = [
            '10985' => '#0284c7', // ชานุมาน (Sky Blue)
            '10986' => '#10b981', // ปทุมราชวงศา (Emerald)
            '10987' => '#8b5cf6', // พนา (Purple)
            '10988' => '#f59e0b', // เสนางคนิคม (Amber)
            '10989' => '#ec4899', // หัวตะพาน (Pink)
            '10990' => '#06b6d4', // ลืออำนาจ (Cyan)
        ];
        $defaultPalette = ['#0284c7', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#6366f1', '#14b8a6', '#ef4444'];

        $monthLabels = [];
        $monthKeys = [];
        $thaiMonthShort = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.',
        ];

        $fDate = Carbon::parse($fiscal_start)->startOfMonth();
        $fEnd  = Carbon::parse($fiscal_end)->startOfMonth();
        while ($fDate <= $fEnd && count($monthKeys) < 12) {
            $ym = $fDate->format('Y-m');
            $m = $fDate->format('m');
            $beYear = ($fDate->year + 543) % 100;
            $monthKeys[] = $ym;
            $monthLabels[] = ($thaiMonthShort[$m] ?? $m) . ' ' . $beYear;
            $fDate->addMonth();
        }

        // OPD Monthly Data for Chart
        $monthlyOpdQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$fiscal_start, $fiscal_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($monthlyOpdQ, 's');
        $monthlyOpdRaw = $monthlyOpdQ
            ->selectRaw("s.hospcode, DATE_FORMAT(s.vstdate, '%Y-%m') as ym, COALESCE(SUM(s.visit_total), 0) as visits")
            ->groupBy('s.hospcode', 'ym')
            ->get();

        $monthlyOpdMatrix = [];
        foreach ($monthlyOpdRaw as $row) {
            $monthlyOpdMatrix[$row->hospcode][$row->ym] = (int)$row->visits;
        }

        $opdChartDatasets = [];
        $monthlyOpdTotals = array_fill_keys($monthKeys, 0);

        $cIdx = 0;
        foreach ($hospitalsList as $hcode => $hname) {
            $data = [];
            foreach ($monthKeys as $ym) {
                $val = $monthlyOpdMatrix[$hcode][$ym] ?? 0;
                $data[] = $val;
                $monthlyOpdTotals[$ym] += $val;
            }
            $opdChartDatasets[] = [
                'type' => 'bar',
                'label' => $hname,
                'backgroundColor' => $hospColors[$hcode] ?? $defaultPalette[$cIdx % count($defaultPalette)],
                'borderRadius' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
            $cIdx++;
        }

        $opdChartDatasets[] = [
            'type' => 'line',
            'label' => 'ยอดรวม OPOH (ครั้ง)',
            'borderColor' => '#0f172a',
            'backgroundColor' => 'rgba(15, 23, 42, 0.08)',
            'borderWidth' => 3,
            'pointBackgroundColor' => '#0f172a',
            'pointRadius' => 4,
            'pointHoverRadius' => 6,
            'tension' => 0.3,
            'fill' => false,
            'data' => array_values($monthlyOpdTotals),
        ];

        $opdMonthlyChart = [
            'labels' => $monthLabels,
            'datasets' => $opdChartDatasets,
        ];

        // IPD Monthly Data for Chart (ไม่นับซ้ำราย AN)
        $monthlyIpdRaw = $this->ipdHospitalRowsQuery($communityCodes, $fiscal_start, $fiscal_end)
            ->selectRaw("hospcode, DATE_FORMAT(dchdate, '%Y-%m') as ym, COALESCE(SUM(an_total), 0) as ans")
            ->groupBy('hospcode', 'ym')
            ->get();

        $monthlyIpdMatrix = [];
        foreach ($monthlyIpdRaw as $row) {
            $monthlyIpdMatrix[$row->hospcode][$row->ym] = (int)$row->ans;
        }

        $ipdChartDatasets = [];
        $monthlyIpdTotals = array_fill_keys($monthKeys, 0);

        $cIdx = 0;
        foreach ($hospitalsList as $hcode => $hname) {
            $data = [];
            foreach ($monthKeys as $ym) {
                $val = $monthlyIpdMatrix[$hcode][$ym] ?? 0;
                $data[] = $val;
                $monthlyIpdTotals[$ym] += $val;
            }
            $ipdChartDatasets[] = [
                'type' => 'bar',
                'label' => $hname,
                'backgroundColor' => $hospColors[$hcode] ?? $defaultPalette[$cIdx % count($defaultPalette)],
                'borderRadius' => 4,
                'minBarLength' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
            $cIdx++;
        }

        $ipdChartDatasets[] = [
            'type' => 'line',
            'label' => 'ยอดรวม IPD (ราย)',
            'borderColor' => '#e11d48',
            'backgroundColor' => 'rgba(225, 29, 72, 0.08)',
            'borderWidth' => 3,
            'pointBackgroundColor' => '#e11d48',
            'pointRadius' => 5,
            'pointHoverRadius' => 7,
            'tension' => 0.3,
            'fill' => false,
            'data' => array_values($monthlyIpdTotals),
        ];

        $ipdMonthlyChart = [
            'labels' => $monthLabels,
            'datasets' => $ipdChartDatasets,
        ];

        // 5.7 Operation / Surgery Service Aggregations (Tab 3: ผ่าตัดและหัตถการ)
        $active_tab = $request->input('active_tab', 'opd');
        if (!in_array($active_tab, ['opd', 'ipd', 'operation', 'income'])) {
            $active_tab = 'opd';
        }

        $opSummaryQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opSummaryQ, 's');
        $opSummary = $opSummaryQ->selectRaw("
                COALESCE(SUM(s.case_total), 0) as total,
                COALESCE(SUM(CASE WHEN s.op_type = 'IPD' THEN s.case_total ELSE 0 END), 0) as total_ipd,
                COALESCE(SUM(CASE WHEN s.op_type = 'ODS' THEN s.case_total ELSE 0 END), 0) as total_ods,
                0 as total_inc
            ")
            ->first();

        $opSumCumulativeQ = DB::table('opoh_operation_service as s')
            ->where('s.operation_date', '>=', $cumulative_start)
            ->where('s.operation_date', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opSumCumulativeQ, 's');
        $opSumCumulative = (int)$opSumCumulativeQ->sum('s.case_total');

        // ยอดสะสมผ่าตัด แยก IPD/ODS (ต.ค. 68 ถึงปัจจุบัน) สำหรับ badge หัวการ์ด
        $opCumSummaryQ = DB::table('opoh_operation_service as s')
            ->where('s.operation_date', '>=', $cumulative_start)
            ->where('s.operation_date', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opCumSummaryQ, 's');
        $opCumSummary = $opCumSummaryQ->selectRaw("
                COALESCE(SUM(s.case_total), 0) as total,
                COALESCE(SUM(CASE WHEN s.op_type = 'IPD' THEN s.case_total ELSE 0 END), 0) as total_ipd,
                COALESCE(SUM(CASE WHEN s.op_type = 'ODS' THEN s.case_total ELSE 0 END), 0) as total_ods
            ")
            ->first();

        // ยอดสะสมผ่าตัดแยกสาขา (ต.ค. 68 ถึงปัจจุบัน) สำหรับการ์ดสาขา
        $opSpecCumulativeQ = DB::table('opoh_operation_service as s')
            ->where('s.operation_date', '>=', $cumulative_start)
            ->where('s.operation_date', '<=', $cumulative_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opSpecCumulativeQ, 's');
        $opSpecCumulative = $opSpecCumulativeQ
            ->groupBy('s.spclty_code')
            ->selectRaw('s.spclty_code as k, COALESCE(SUM(s.case_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // Top 5 Diagnoses & Operations for Right-Hand (5-Col) Cards
        $topOpdDiagnosesQ = DB::table('opoh_opd_diagnoses as d')
            ->whereBetween('d.vstdate', [$current_start, $current_end])
            ->whereIn('d.hospcode', $communityCodes);
        $this->applyOriginExclusion($topOpdDiagnosesQ, 'd');
        $topOpdDiagnoses = $topOpdDiagnosesQ
            ->selectRaw('d.icd10, d.diag_name, COALESCE(SUM(d.case_total), 0) as total')
            ->groupBy('d.icd10', 'd.diag_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topIpdDiagnosesQ = DB::table('opoh_ipd_diagnoses as d')
            ->whereBetween('d.dchdate', [$current_start, $current_end])
            ->whereIn('d.hospcode', $communityCodes);
        $this->applyOriginExclusion($topIpdDiagnosesQ, 'd');
        $topIpdDiagnoses = $topIpdDiagnosesQ
            ->selectRaw('d.icd10, d.diag_name, COALESCE(SUM(d.case_total), 0) as total')
            ->groupBy('d.icd10', 'd.diag_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topOperationsQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($topOperationsQ, 's');
        $topOperations = $topOperationsQ
            ->selectRaw('s.icd9, s.operation_name, COALESCE(SUM(s.case_total), 0) as total')
            ->groupBy('s.icd9', 's.operation_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Operation Monthly Data for Chart (Tab 3)
        $monthlyOpQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$fiscal_start, $fiscal_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($monthlyOpQ, 's');
        $monthlyOpRaw = $monthlyOpQ
            ->selectRaw("s.hospcode, DATE_FORMAT(s.operation_date, '%Y-%m') as ym, COALESCE(SUM(s.case_total), 0) as cases")
            ->groupBy('s.hospcode', 'ym')
            ->get();

        $monthlyOpMatrix = [];
        foreach ($monthlyOpRaw as $row) {
            $monthlyOpMatrix[$row->hospcode][$row->ym] = (int)$row->cases;
        }

        $opChartDatasets = [];
        $monthlyOpTotals = array_fill_keys($monthKeys, 0);

        $cIdx = 0;
        foreach ($hospitalsList as $hcode => $hname) {
            $data = [];
            foreach ($monthKeys as $ym) {
                $val = $monthlyOpMatrix[$hcode][$ym] ?? 0;
                $data[] = $val;
                $monthlyOpTotals[$ym] += $val;
            }
            $opChartDatasets[] = [
                'type' => 'bar',
                'label' => $hname,
                'backgroundColor' => $hospColors[$hcode] ?? $defaultPalette[$cIdx % count($defaultPalette)],
                'borderRadius' => 4,
                'minBarLength' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
            $cIdx++;
        }

        $opChartDatasets[] = [
            'type' => 'line',
            'label' => 'ยอดรวมผ่าตัด (เคส)',
            'borderColor' => '#8b5cf6',
            'backgroundColor' => 'rgba(139, 92, 246, 0.08)',
            'borderWidth' => 3,
            'pointBackgroundColor' => '#8b5cf6',
            'pointRadius' => 5,
            'pointHoverRadius' => 7,
            'tension' => 0.3,
            'fill' => false,
            'data' => array_values($monthlyOpTotals),
        ];

        $opMonthlyChart = [
            'labels' => $monthLabels,
            'datasets' => $opChartDatasets,
        ];

        // 4 Surgical Specialties for Tab 3: จักษุ (08), ศัลยกรรมทั่วไป (02), ศัลยกรรมกระดูก (06), สูตินรีเวชกรรม (03)
        $surgicalSpecialties = [
            '08' => $specialties['08'],
            '02' => $specialties['02'],
            '06' => $specialties['06'],
            '03' => $specialties['03'],
        ];

        $opSpecHospMatrixQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$current_start, $current_end])
            ->whereIn('s.hospcode', $allHospCodes);
        $this->applyOriginExclusion($opSpecHospMatrixQ, 's', 'hospcode', 'doctor_id', $hubHospitalCode);
        $opSpecHospMatrixRaw = $opSpecHospMatrixQ
            ->groupBy('s.spclty_code', 's.hospcode')
            ->selectRaw('s.spclty_code, s.hospcode, COALESCE(SUM(s.case_total), 0) as cases')
            ->get();

        $opSpecHospMatrix = [];
        foreach ($opSpecHospMatrixRaw as $m) {
            $opSpecHospMatrix[$m->spclty_code][$m->hospcode] = (int)$m->cases;
        }

        // Dynamically include any other specialty that has operations recorded so nothing is ever omitted
        foreach ($opSpecHospMatrix as $code => $hospData) {
            if (!isset($surgicalSpecialties[$code])) {
                $surgicalSpecialties[$code] = $specialties[$code] ?? [
                    'name'  => 'สาขา ' . $code,
                    'color' => '#64748b',
                    'icon'  => 'fa-notes-medical',
                ];
            }
        }

        $opSpecialtyRows = [];
        $opSpecColTotals = [
            'period' => 0,
            'amnat'  => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $opSpecColTotals[$hcode] = 0;
        }

        foreach ($surgicalSpecialties as $code => $spec) {
            $period = 0;
            $hospCases = [];
            foreach ($hospitalsList as $hcode => $hname) {
                $cnt = (int)($opSpecHospMatrix[$code][$hcode] ?? 0);
                $hospCases[$hcode] = $cnt;
                $opSpecColTotals[$hcode] += $cnt;
                $period += $cnt;
            }
            $amnatCases = (int)($opSpecHospMatrix[$code][$hubHospitalCode] ?? 0);
            $opSpecColTotals['period'] += $period;
            $opSpecColTotals['amnat'] += $amnatCases;

            $opSpecialtyRows[] = [
                'code'        => $code,
                'name'        => $spec['name'],
                'color'       => $spec['color'],
                'icon'        => $spec['icon'],
                'period'      => $period,
                'cumulative'  => (int)($opSpecCumulative[$code] ?? 0),
                'hosp_cases'  => $hospCases,
                'amnat_cases' => $amnatCases,
            ];
        }

        // 5.8 การจัดสรรและสรุปข้อมูลแยกตาม 6 สิทธิการรักษามาตรฐาน (OPD, IPD, Surgery)
        $rightsConfig = [
            'ucs_incup' => [
                'name'  => 'ประกันสุขภาพ ในจังหวัด',
                'sub'   => 'สิทธิบัตรทอง (UCS ใน CUP)',
                'badge' => 'UC ในจังหวัด',
                'color' => '#0284c7',
                'bg'    => 'rgba(2, 132, 199, 0.08)',
                'icon'  => 'fa-id-card',
                'rate'  => 8350, // Base Rate DRG (บาท/AdjRW)
            ],
            'ucs_outprov' => [
                'name'  => 'ประกันสุขภาพ ต่างจังหวัด',
                'sub'   => 'สิทธิบัตรทอง (UCS นอก CUP/ต่างจังหวัด)',
                'badge' => 'UC ต่างจังหวัด',
                'color' => '#0891b2',
                'bg'    => 'rgba(8, 145, 178, 0.08)',
                'icon'  => 'fa-map-location-dot',
                'rate'  => 8350,
            ],
            'ofc' => [
                'name'  => 'ข้าราชการ',
                'sub'   => 'เบิกจ่ายตรงกรมบัญชีกลาง (OFC)',
                'badge' => 'ข้าราชการ (OFC)',
                'color' => '#16a34a',
                'bg'    => 'rgba(22, 163, 74, 0.08)',
                'icon'  => 'fa-building-columns',
                'rate'  => 10500,
            ],
            'lgo' => [
                'name'  => 'อปท.',
                'sub'   => 'องค์กรปกครองส่วนท้องถิ่น (LGO)',
                'badge' => 'อปท. (LGO)',
                'color' => '#ea580c',
                'bg'    => 'rgba(234, 88, 12, 0.08)',
                'icon'  => 'fa-landmark',
                'rate'  => 10500,
            ],
            'sss' => [
                'name'  => 'ประกันสังคม',
                'sub'   => 'ผู้ประกันตน ม.33, 39, 40 (SSS)',
                'badge' => 'ประกันสังคม (SSS)',
                'color' => '#9333ea',
                'bg'    => 'rgba(147, 51, 234, 0.08)',
                'icon'  => 'fa-hospital-user',
                'rate'  => 12000,
            ],
            'other' => [
                'name'  => 'อื่น ๆ',
                'sub'   => 'จ่ายเงินเอง, พ.ร.บ., ต่างด้าว, อื่นๆ',
                'badge' => 'ชำระเงิน/อื่น ๆ',
                'color' => '#64748b',
                'bg'    => 'rgba(100, 116, 139, 0.08)',
                'icon'  => 'fa-users',
                'rate'  => 0,
            ],
        ];

        // 5.8.1 OPD 6 Rights Summary
        $opdRawRightsQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opdRawRightsQ, 's');
        $opdRawRights = $opdRawRightsQ->selectRaw("
                COALESCE(SUM(s.visit_ucs_incup), 0)   AS v_ucs_incup,
                COALESCE(SUM(s.visit_ucs_outprov), 0) AS v_ucs_outprov,
                COALESCE(SUM(s.visit_ucs), 0)         AS v_ucs_total,
                COALESCE(SUM(s.visit_ofc), 0)         AS v_ofc,
                COALESCE(SUM(s.visit_lgo), 0)         AS v_lgo,
                COALESCE(SUM(s.visit_sss), 0)         AS v_sss,
                COALESCE(SUM(s.visit_other), 0)       AS v_other,
                COALESCE(SUM(s.visit_total), 0)       AS v_total
            ")
            ->first();

        $opdRightsInc = $this->incomeCategoriesQuery('OPD', $communityCodes, $current_start, $current_end)
            ->selectRaw("
                COALESCE(SUM(inc_ucs_incup), 0)     AS inc_ucs_incup,
                COALESCE(SUM(inc_ucs_outprov), 0)   AS inc_ucs_outprov,
                COALESCE(SUM(inc_ofc), 0)           AS inc_ofc,
                COALESCE(SUM(inc_lgo), 0)           AS inc_lgo,
                COALESCE(SUM(inc_sss), 0)           AS inc_sss,
                COALESCE(SUM(inc_other), 0)         AS inc_other,
                COALESCE(SUM(inc_total), 0)         AS inc_total
            ")
            ->first();

        $opdVisitsTotal = (int)($opdRawRights->v_total ?? 0);
        $opdIncTotal    = (float)($opdRightsInc->inc_total ?? 0.0);

        // Fallback if legacy records had total visit_ucs but not yet split
        $vUcsIncup   = (int)($opdRawRights->v_ucs_incup ?? 0);
        $vUcsOutprov = (int)($opdRawRights->v_ucs_outprov ?? 0);
        if ($vUcsIncup == 0 && $vUcsOutprov == 0 && ($opdRawRights->v_ucs_total ?? 0) > 0) {
            $vUcsIncup = (int)$opdRawRights->v_ucs_total;
        }

        $incUcsIncup   = (float)($opdRightsInc->inc_ucs_incup ?? 0.0);
        $incUcsOutprov = (float)($opdRightsInc->inc_ucs_outprov ?? 0.0);
        $incOfc        = (float)($opdRightsInc->inc_ofc ?? 0.0);
        $incLgo        = (float)($opdRightsInc->inc_lgo ?? 0.0);
        $incSss        = (float)($opdRightsInc->inc_sss ?? 0.0);
        $incOther      = (float)($opdRightsInc->inc_other ?? 0.0);

        $opdRightsRows = [
            'ucs_incup'   => ['visits' => $vUcsIncup, 'inc' => $incUcsIncup],
            'ucs_outprov' => ['visits' => $vUcsOutprov, 'inc' => $incUcsOutprov],
            'ofc'         => ['visits' => (int)($opdRawRights->v_ofc ?? 0), 'inc' => $incOfc],
            'lgo'         => ['visits' => (int)($opdRawRights->v_lgo ?? 0), 'inc' => $incLgo],
            'sss'         => ['visits' => (int)($opdRawRights->v_sss ?? 0), 'inc' => $incSss],
            'other'       => ['visits' => (int)($opdRawRights->v_other ?? 0), 'inc' => $incOther],
        ];

        // 5.8.2 IPD 6 Rights Summary (ไม่นับซ้ำราย AN + กรอง รพ.ต้นสังกัด แยกตาม AdjRW และ CMI สำหรับ Clearing)
        $ipdRawRights = $this->ipdHospitalRowsQuery($communityCodes, $current_start, $current_end)
            ->selectRaw("
                COALESCE(SUM(an_ucs_incup), 0)        AS an_ucs_incup,
                COALESCE(SUM(an_ucs_outprov), 0)      AS an_ucs_outprov,
                COALESCE(SUM(an_ofc), 0)              AS an_ofc,
                COALESCE(SUM(an_lgo), 0)              AS an_lgo,
                COALESCE(SUM(an_sss), 0)              AS an_sss,
                COALESCE(SUM(an_other), 0)            AS an_other,
                COALESCE(SUM(an_total), 0)            AS an_total,

                COALESCE(SUM(admdate_ucs_incup), 0)   AS los_ucs_incup,
                COALESCE(SUM(admdate_ucs_outprov), 0) AS los_ucs_outprov,
                COALESCE(SUM(admdate_ofc), 0)         AS los_ofc,
                COALESCE(SUM(admdate_lgo), 0)         AS los_lgo,
                COALESCE(SUM(admdate_sss), 0)         AS los_sss,
                COALESCE(SUM(admdate_other), 0)       AS los_other,
                COALESCE(SUM(admdate_total), 0)       AS los_total,

                COALESCE(SUM(adjrw_ucs_incup), 0)     AS adjrw_ucs_incup,
                COALESCE(SUM(adjrw_ucs_outprov), 0)   AS adjrw_ucs_outprov,
                COALESCE(SUM(adjrw_ofc), 0)           AS adjrw_ofc,
                COALESCE(SUM(adjrw_lgo), 0)           AS adjrw_lgo,
                COALESCE(SUM(adjrw_sss), 0)           AS adjrw_sss,
                COALESCE(SUM(adjrw_other), 0)         AS adjrw_other,
                COALESCE(SUM(adjrw_total), 0)         AS adjrw_total
            ")
            ->first();

        $ipdRightsInc = $this->incomeCategoriesQuery('IPD', $communityCodes, $current_start, $current_end)
            ->selectRaw("
                COALESCE(SUM(inc_ucs_incup), 0)     AS inc_ucs_incup,
                COALESCE(SUM(inc_ucs_outprov), 0)   AS inc_ucs_outprov,
                COALESCE(SUM(inc_ofc), 0)           AS inc_ofc,
                COALESCE(SUM(inc_lgo), 0)           AS inc_lgo,
                COALESCE(SUM(inc_sss), 0)           AS inc_sss,
                COALESCE(SUM(inc_other), 0)         AS inc_other,
                COALESCE(SUM(inc_total), 0)         AS inc_total
            ")
            ->first();

        $ipdAnsTotal   = (int)($ipdRawRights->an_total ?? 0);
        $ipdLosTotal   = (int)($ipdRawRights->los_total ?? 0);
        $ipdRwTotal    = (float)($ipdRawRights->adjrw_total ?? 0.0);
        $ipdIncTotal   = (float)($ipdRightsInc->inc_total ?? 0.0);

        // Fallback for legacy IPD records
        $anUcsIncup    = (int)($ipdRawRights->an_ucs_incup ?? 0);
        $anUcsOutprov  = (int)($ipdRawRights->an_ucs_outprov ?? 0);
        $losUcsIncup   = (int)($ipdRawRights->los_ucs_incup ?? 0);
        $adjrwUcsIncup = (float)($ipdRawRights->adjrw_ucs_incup ?? 0.0);
        $incUcsIncup   = (float)($ipdRightsInc->inc_ucs_incup ?? 0.0);

        $allRightsSum = $anUcsIncup + $anUcsOutprov + (int)($ipdRawRights->an_ofc ?? 0) + (int)($ipdRawRights->an_lgo ?? 0) + (int)($ipdRawRights->an_sss ?? 0) + (int)($ipdRawRights->an_other ?? 0);
        if ($allRightsSum == 0 && $ipdAnsTotal > 0) {
            $anUcsIncup    = $ipdAnsTotal;
            $losUcsIncup   = $ipdLosTotal;
            $adjrwUcsIncup = $ipdRwTotal;
            $incUcsIncup   = $ipdIncTotal;
        }

        $ipdRightsRows = [
            'ucs_incup'   => [
                'ans'   => $anUcsIncup,
                'los'   => $losUcsIncup,
                'adjrw' => $adjrwUcsIncup,
                'cmi'   => $anUcsIncup > 0 ? ($adjrwUcsIncup / $anUcsIncup) : 0,
                'inc'   => $incUcsIncup,
            ],
            'ucs_outprov' => [
                'ans'   => $anUcsOutprov,
                'los'   => (int)($ipdRawRights->los_ucs_outprov ?? 0),
                'adjrw' => (float)($ipdRawRights->adjrw_ucs_outprov ?? 0.0),
                'cmi'   => $anUcsOutprov > 0 ? ((float)($ipdRawRights->adjrw_ucs_outprov ?? 0.0) / $anUcsOutprov) : 0,
                'inc'   => (float)($ipdRightsInc->inc_ucs_outprov ?? 0.0),
            ],
            'ofc'         => [
                'ans'   => (int)($ipdRawRights->an_ofc ?? 0),
                'los'   => (int)($ipdRawRights->los_ofc ?? 0),
                'adjrw' => (float)($ipdRawRights->adjrw_ofc ?? 0.0),
                'cmi'   => (int)($ipdRawRights->an_ofc ?? 0) > 0 ? ((float)($ipdRawRights->adjrw_ofc ?? 0.0) / (int)$ipdRawRights->an_ofc) : 0,
                'inc'   => (float)($ipdRightsInc->inc_ofc ?? 0.0),
            ],
            'lgo'         => [
                'ans'   => (int)($ipdRawRights->an_lgo ?? 0),
                'los'   => (int)($ipdRawRights->los_lgo ?? 0),
                'adjrw' => (float)($ipdRawRights->adjrw_lgo ?? 0.0),
                'cmi'   => (int)($ipdRawRights->an_lgo ?? 0) > 0 ? ((float)($ipdRawRights->adjrw_lgo ?? 0.0) / (int)$ipdRawRights->an_lgo) : 0,
                'inc'   => (float)($ipdRightsInc->inc_lgo ?? 0.0),
            ],
            'sss'         => [
                'ans'   => (int)($ipdRawRights->an_sss ?? 0),
                'los'   => (int)($ipdRawRights->los_sss ?? 0),
                'adjrw' => (float)($ipdRawRights->adjrw_sss ?? 0.0),
                'cmi'   => (int)($ipdRawRights->an_sss ?? 0) > 0 ? ((float)($ipdRawRights->adjrw_sss ?? 0.0) / (int)$ipdRawRights->an_sss) : 0,
                'inc'   => (float)($ipdRightsInc->inc_sss ?? 0.0),
            ],
            'other'       => [
                'ans'   => (int)($ipdRawRights->an_other ?? 0),
                'los'   => (int)($ipdRawRights->los_other ?? 0),
                'adjrw' => (float)($ipdRawRights->adjrw_other ?? 0.0),
                'cmi'   => (int)($ipdRawRights->an_other ?? 0) > 0 ? ((float)($ipdRawRights->adjrw_other ?? 0.0) / (int)$ipdRawRights->an_other) : 0,
                'inc'   => (float)($ipdRightsInc->inc_other ?? 0.0),
            ],
        ];

        // 5.8.3 Operation 6 Rights Summary (Tab 3: Surgery)
        $opRawRightsQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($opRawRightsQ, 's');
        $opRawRights = $opRawRightsQ->selectRaw("
                COALESCE(SUM(s.case_ucs_incup), 0)   AS case_ucs_incup,
                COALESCE(SUM(s.case_ucs_outprov), 0) AS case_ucs_outprov,
                COALESCE(SUM(s.case_ofc), 0)         AS case_ofc,
                COALESCE(SUM(s.case_lgo), 0)         AS case_lgo,
                COALESCE(SUM(s.case_sss), 0)         AS case_sss,
                COALESCE(SUM(s.case_other), 0)       AS case_other,
                COALESCE(SUM(s.case_total), 0)       AS case_total,
                COALESCE(SUM(s.adjrw_total), 0)      AS adjrw_total
            ")
            ->first();

        $opCasesTotal = (int)($opRawRights->case_total ?? 0);
        $opIncTotal   = 0.0;
        $opRwTotal    = (float)($opRawRights->adjrw_total ?? 0.0);

        $caseUcsIncup   = (int)($opRawRights->case_ucs_incup ?? 0);
        $caseUcsOutprov = (int)($opRawRights->case_ucs_outprov ?? 0);
        $opRightsSum    = $caseUcsIncup + $caseUcsOutprov + (int)($opRawRights->case_ofc ?? 0) + (int)($opRawRights->case_lgo ?? 0) + (int)($opRawRights->case_sss ?? 0) + (int)($opRawRights->case_other ?? 0);

        if ($opRightsSum == 0 && $opCasesTotal > 0) {
            $caseUcsIncup = $opCasesTotal;
        }

        $opRightsRows = [
            'ucs_incup'   => ['cases' => $caseUcsIncup, 'inc' => 0.0],
            'ucs_outprov' => ['cases' => $caseUcsOutprov, 'inc' => 0.0],
            'ofc'         => ['cases' => (int)($opRawRights->case_ofc ?? 0), 'inc' => 0.0],
            'lgo'         => ['cases' => (int)($opRawRights->case_lgo ?? 0), 'inc' => 0.0],
            'sss'         => ['cases' => (int)($opRawRights->case_sss ?? 0), 'inc' => 0.0],
            'other'       => ['cases' => (int)($opRawRights->case_other ?? 0), 'inc' => 0.0],
        ];

        // 5.8.4 Hospital Breakdown for Modal Popup on Scheme/Card Click (ใช้ $allModalHospitals จาก resolveOpohHospitals)

        // A. OPD Breakdown by Hospcode & Right
        $opdHospQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $allHospCodes);
        $this->applyOriginExclusion($opdHospQ, 's', 'hospcode', 'doctor_id', $hubHospitalCode);
        $opdHospRaw = $opdHospQ
            ->groupBy('s.hospcode')
            ->selectRaw("
                s.hospcode,
                COALESCE(SUM(s.visit_ucs_incup), 0)   AS v_ucs_incup,
                COALESCE(SUM(s.visit_ucs_outprov), 0) AS v_ucs_outprov,
                COALESCE(SUM(s.visit_ucs), 0)         AS v_ucs_total,
                COALESCE(SUM(s.visit_ofc), 0)         AS v_ofc,
                COALESCE(SUM(s.visit_lgo), 0)         AS v_lgo,
                COALESCE(SUM(s.visit_sss), 0)         AS v_sss,
                COALESCE(SUM(s.visit_other), 0)       AS v_other,
                COALESCE(SUM(s.visit_total), 0)       AS v_total
            ")
            ->get()
            ->keyBy('hospcode');

        $opdHospIncRaw = $this->incomeCategoriesQuery('OPD', $allHospCodes, $current_start, $current_end, $hubHospitalCode)
            ->groupBy('hospcode')
            ->selectRaw("
                hospcode,
                COALESCE(SUM(inc_ucs_incup), 0)     AS inc_ucs_incup,
                COALESCE(SUM(inc_ucs_outprov), 0)   AS inc_ucs_outprov,
                COALESCE(SUM(inc_ofc), 0)           AS inc_ofc,
                COALESCE(SUM(inc_lgo), 0)           AS inc_lgo,
                COALESCE(SUM(inc_sss), 0)           AS inc_sss,
                COALESCE(SUM(inc_other), 0)         AS inc_other,
                COALESCE(SUM(inc_total), 0)         AS inc_total
            ")
            ->get()
            ->keyBy('hospcode');

        $opdRightsByHosp = [];
        foreach (array_keys($rightsConfig) as $rKey) {
            $opdRightsByHosp[$rKey] = [];
            foreach ($allModalHospitals as $hcode => $hInfo) {
                $raw = $opdHospRaw[$hcode] ?? null;
                $incRaw = $opdHospIncRaw[$hcode] ?? null;
                $v = 0;
                $inc = 0.0;
                if ($raw) {
                    if ($rKey === 'ucs_incup') {
                        $v = (int)$raw->v_ucs_incup;
                        if ($v == 0 && (int)$raw->v_ucs_outprov == 0 && (int)$raw->v_ucs_total > 0) {
                            $v = (int)$raw->v_ucs_total;
                        }
                    } elseif ($rKey === 'ucs_outprov') {
                        $v = (int)$raw->v_ucs_outprov;
                    } elseif ($rKey === 'ofc') {
                        $v = (int)$raw->v_ofc;
                    } elseif ($rKey === 'lgo') {
                        $v = (int)$raw->v_lgo;
                    } elseif ($rKey === 'sss') {
                        $v = (int)$raw->v_sss;
                    } elseif ($rKey === 'other') {
                        $v = (int)$raw->v_other;
                    }
                }
                if ($incRaw) {
                    if ($rKey === 'ucs_incup') {
                        $inc = (float)$incRaw->inc_ucs_incup;
                    } elseif ($rKey === 'ucs_outprov') {
                        $inc = (float)$incRaw->inc_ucs_outprov;
                    } elseif ($rKey === 'ofc') {
                        $inc = (float)$incRaw->inc_ofc;
                    } elseif ($rKey === 'lgo') {
                        $inc = (float)$incRaw->inc_lgo;
                    } elseif ($rKey === 'sss') {
                        $inc = (float)$incRaw->inc_sss;
                    } elseif ($rKey === 'other') {
                        $inc = (float)$incRaw->inc_other;
                    }
                }
                $opdRightsByHosp[$rKey][$hcode] = [
                    'hospcode' => $hcode,
                    'name'     => $hInfo['name'],
                    'type'     => $hInfo['type'],
                    'visits'   => $v,
                    'inc'      => $inc,
                ];
            }
        }

        // B. IPD Breakdown by Hospcode & Right (รพช. ใช้ยอดไม่นับซ้ำ AN + กรองต้นสังกัด, แม่ข่ายแสดงยอดอ้างอิงทั้งหมด)
        $ipdHospSelectCols = "
                hospcode,
                COALESCE(SUM(an_ucs_incup), 0)        AS an_ucs_incup,
                COALESCE(SUM(an_ucs_outprov), 0)      AS an_ucs_outprov,
                COALESCE(SUM(an_ofc), 0)              AS an_ofc,
                COALESCE(SUM(an_lgo), 0)              AS an_lgo,
                COALESCE(SUM(an_sss), 0)              AS an_sss,
                COALESCE(SUM(an_other), 0)            AS an_other,
                COALESCE(SUM(an_total), 0)            AS an_total,

                COALESCE(SUM(admdate_ucs_incup), 0)   AS los_ucs_incup,
                COALESCE(SUM(admdate_ucs_outprov), 0) AS los_ucs_outprov,
                COALESCE(SUM(admdate_ofc), 0)         AS los_ofc,
                COALESCE(SUM(admdate_lgo), 0)         AS los_lgo,
                COALESCE(SUM(admdate_sss), 0)         AS los_sss,
                COALESCE(SUM(admdate_other), 0)       AS los_other,
                COALESCE(SUM(admdate_total), 0)       AS los_total,

                COALESCE(SUM(adjrw_ucs_incup), 0)     AS adjrw_ucs_incup,
                COALESCE(SUM(adjrw_ucs_outprov), 0)   AS adjrw_ucs_outprov,
                COALESCE(SUM(adjrw_ofc), 0)           AS adjrw_ofc,
                COALESCE(SUM(adjrw_lgo), 0)           AS adjrw_lgo,
                COALESCE(SUM(adjrw_sss), 0)           AS adjrw_sss,
                COALESCE(SUM(adjrw_other), 0)         AS adjrw_other,
                COALESCE(SUM(adjrw_total), 0)         AS adjrw_total
            ";
        $ipdHospRaw = $this->ipdHospitalRowsQuery($communityCodes, $current_start, $current_end)
            ->groupBy('hospcode')
            ->selectRaw($ipdHospSelectCols)
            ->get()
            ->keyBy('hospcode');

        $ipdHubHospRow = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->groupBy('hospcode')
            ->selectRaw($ipdHospSelectCols)
            ->first();
        if ($ipdHubHospRow) {
            $ipdHospRaw->put($hubHospitalCode, $ipdHubHospRow);
        }

        $ipdIncSelectCols = "
                hospcode,
                COALESCE(SUM(inc_ucs_incup), 0)     AS inc_ucs_incup,
                COALESCE(SUM(inc_ucs_outprov), 0)   AS inc_ucs_outprov,
                COALESCE(SUM(inc_ofc), 0)           AS inc_ofc,
                COALESCE(SUM(inc_lgo), 0)           AS inc_lgo,
                COALESCE(SUM(inc_sss), 0)           AS inc_sss,
                COALESCE(SUM(inc_other), 0)         AS inc_other,
                COALESCE(SUM(inc_total), 0)         AS inc_total
            ";
        $ipdHospIncRaw = $this->incomeCategoriesQuery('IPD', $communityCodes, $current_start, $current_end)
            ->groupBy('hospcode')
            ->selectRaw($ipdIncSelectCols)
            ->get()
            ->keyBy('hospcode');

        $ipdHubHospIncRow = DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->groupBy('hospcode')
            ->selectRaw($ipdIncSelectCols)
            ->first();
        if ($ipdHubHospIncRow) {
            $ipdHospIncRaw->put($hubHospitalCode, $ipdHubHospIncRow);
        }

        $ipdRightsByHosp = [];
        foreach (array_keys($rightsConfig) as $rKey) {
            $ipdRightsByHosp[$rKey] = [];
            $rate = $rightsConfig[$rKey]['rate'] ?? 0;
            foreach ($allModalHospitals as $hcode => $hInfo) {
                $raw = $ipdHospRaw[$hcode] ?? null;
                $incRaw = $ipdHospIncRaw[$hcode] ?? null;
                $ans = 0;
                $los = 0;
                $adjrw = 0.0;
                $inc = 0.0;
                if ($raw) {
                    if ($rKey === 'ucs_incup') {
                        $ans = (int)$raw->an_ucs_incup;
                        $los = (int)$raw->los_ucs_incup;
                        $adjrw = (float)$raw->adjrw_ucs_incup;
                        $sumOtherRights = (int)$raw->an_ucs_outprov + (int)$raw->an_ofc + (int)$raw->an_lgo + (int)$raw->an_sss + (int)$raw->an_other;
                        if ($ans == 0 && $sumOtherRights == 0 && (int)$raw->an_total > 0) {
                            $ans = (int)$raw->an_total;
                            $los = (int)$raw->los_total;
                            $adjrw = (float)$raw->adjrw_total;
                        }
                    } elseif ($rKey === 'ucs_outprov') {
                        $ans = (int)$raw->an_ucs_outprov;
                        $los = (int)$raw->los_ucs_outprov;
                        $adjrw = (float)$raw->adjrw_ucs_outprov;
                    } elseif ($rKey === 'ofc') {
                        $ans = (int)$raw->an_ofc;
                        $los = (int)$raw->los_ofc;
                        $adjrw = (float)$raw->adjrw_ofc;
                    } elseif ($rKey === 'lgo') {
                        $ans = (int)$raw->an_lgo;
                        $los = (int)$raw->los_lgo;
                        $adjrw = (float)$raw->adjrw_lgo;
                    } elseif ($rKey === 'sss') {
                        $ans = (int)$raw->an_sss;
                        $los = (int)$raw->los_sss;
                        $adjrw = (float)$raw->adjrw_sss;
                    } elseif ($rKey === 'other') {
                        $ans = (int)$raw->an_other;
                        $los = (int)$raw->los_other;
                        $adjrw = (float)$raw->adjrw_other;
                    }
                }
                if ($incRaw) {
                    if ($rKey === 'ucs_incup') {
                        $inc = (float)$incRaw->inc_ucs_incup;
                    } elseif ($rKey === 'ucs_outprov') {
                        $inc = (float)$incRaw->inc_ucs_outprov;
                    } elseif ($rKey === 'ofc') {
                        $inc = (float)$incRaw->inc_ofc;
                    } elseif ($rKey === 'lgo') {
                        $inc = (float)$incRaw->inc_lgo;
                    } elseif ($rKey === 'sss') {
                        $inc = (float)$incRaw->inc_sss;
                    } elseif ($rKey === 'other') {
                        $inc = (float)$incRaw->inc_other;
                    }
                }
                $cmi = $ans > 0 ? ($adjrw / $ans) : 0.0;
                $estComp = ($rate > 0) ? ($adjrw * $rate) : 0.0;
                $ipdRightsByHosp[$rKey][$hcode] = [
                    'hospcode' => $hcode,
                    'name'     => $hInfo['name'],
                    'type'     => $hInfo['type'],
                    'ans'      => $ans,
                    'los'      => $los,
                    'adjrw'    => $adjrw,
                    'cmi'      => $cmi,
                    'est_comp' => $estComp,
                    'inc'      => $inc,
                ];
            }
        }

        // C. Surgery Breakdown by Hospcode & Right
        $opHospQ = DB::table('opoh_operation_service as s')
            ->whereBetween('s.operation_date', [$current_start, $current_end])
            ->whereIn('s.hospcode', $allHospCodes);
        $this->applyOriginExclusion($opHospQ, 's', 'hospcode', 'doctor_id', $hubHospitalCode);
        $opHospRaw = $opHospQ
            ->groupBy('s.hospcode')
            ->selectRaw("
                s.hospcode,
                COALESCE(SUM(s.case_ucs_incup), 0)   AS case_ucs_incup,
                COALESCE(SUM(s.case_ucs_outprov), 0) AS case_ucs_outprov,
                COALESCE(SUM(s.case_ofc), 0)         AS case_ofc,
                COALESCE(SUM(s.case_lgo), 0)         AS case_lgo,
                COALESCE(SUM(s.case_sss), 0)         AS case_sss,
                COALESCE(SUM(s.case_other), 0)       AS case_other,
                COALESCE(SUM(s.case_total), 0)       AS case_total,
                COALESCE(SUM(s.adjrw_total), 0)      AS adjrw_total
            ")
            ->get()
            ->keyBy('hospcode');

        $opRightsByHosp = [];
        foreach (array_keys($rightsConfig) as $rKey) {
            $opRightsByHosp[$rKey] = [];
            foreach ($allModalHospitals as $hcode => $hInfo) {
                $raw = $opHospRaw[$hcode] ?? null;
                $cases = 0;
                $adjrw = 0.0;
                if ($raw) {
                    if ($rKey === 'ucs_incup') {
                        $cases = (int)$raw->case_ucs_incup;
                        $adjrw = (float)$raw->adjrw_total;
                        $sumOther = (int)$raw->case_ucs_outprov + (int)$raw->case_ofc + (int)$raw->case_lgo + (int)$raw->case_sss + (int)$raw->case_other;
                        if ($cases == 0 && $sumOther == 0 && (int)$raw->case_total > 0) {
                            $cases = (int)$raw->case_total;
                        }
                    } elseif ($rKey === 'ucs_outprov') {
                        $cases = (int)$raw->case_ucs_outprov;
                    } elseif ($rKey === 'ofc') {
                        $cases = (int)$raw->case_ofc;
                    } elseif ($rKey === 'lgo') {
                        $cases = (int)$raw->case_lgo;
                    } elseif ($rKey === 'sss') {
                        $cases = (int)$raw->case_sss;
                    } elseif ($rKey === 'other') {
                        $cases = (int)$raw->case_other;
                    }
                }
                $opRightsByHosp[$rKey][$hcode] = [
                    'hospcode' => $hcode,
                    'name'     => $hInfo['name'],
                    'type'     => $hInfo['type'],
                    'cases'    => $cases,
                    'adjrw'    => $adjrw,
                    'inc'      => 0.0,
                ];
            }
        }

        $allHospitalsJson = $allModalHospitals;

        // 6. Doctor Roster for Management Modal
        $doctors = OpohDoctor::orderBy('spclty_code')->orderBy('id')->get();

        // 7. Permission checks & Thai Date formatting
        $canManageDoctors = Auth::check() && Auth::user()->canAccessOpoh();

        $formatThaiDate = function($dateStr, $full = false) {
            if (!$dateStr) return '';
            $d = Carbon::parse($dateStr)->locale('th');
            $yearBe = $d->year + 543;
            $format = $full ? 'D MMMM' : 'D MMM';
            return $d->isoFormat($format) . ' ' . $yearBe;
        };

        $thaiDateRangeText = $formatThaiDate($current_start) . ' ถึง ' . $formatThaiDate($current_end);

        // 8. 16 Medical Income Categories (Tab 4: หมวดค่ารักษาพยาบาล)
        $masterCategories = [
            1  => '1. ค่าห้องและค่าอาหาร',
            2  => '2. อวัยวะเทียมและอุปกรณ์ในการบำบัดรักษา',
            3  => '3. ยาและสารอาหารทางเส้นเลือดที่ใช้ใน รพ.',
            4  => '4. ยาที่นำไปใช้ต่อที่บ้าน',
            5  => '5. เวชภัณฑ์ที่มิใช่ยา',
            6  => '6. บริการโลหิตและส่วนประกอบของโลหิต',
            7  => '7. ตรวจวินิจฉัยทางเทคนิคการแพทย์และพยาธิวิทยา (Lab)',
            8  => '8. ตรวจวินิจฉัยและรักษาทางรังสีวิทยา (X-Ray)',
            9  => '9. ตรวจวินิจฉัยโดยวิธีพิเศษอื่นๆ',
            10 => '10. อุปกรณ์ของใช้และเครื่องมือทางการแพทย์',
            11 => '11. ทำหัตถการและวิสัญญี',
            12 => '12. ค่าบริการทางการพยาบาล',
            13 => '13. ค่าบริการทางทันตกรรม',
            14 => '14. ค่าบริการทางกายภาพบำบัดและเวชกรรมฟื้นฟู',
            15 => '15. ค่าบริการฝังเข็มและการบำบัดของผู้ประกอบโรคศิลปะอื่น',
            16 => '16. ค่าบริการอื่นๆ ที่ไม่เกี่ยวกับการรักษาพยาบาลโดยตรง',
        ];

        $income_service_type = $request->input('income_service_type', 'ALL'); // 'ALL', 'OPD', 'IPD'
        if (!in_array($income_service_type, ['ALL', 'OPD', 'IPD'])) {
            $income_service_type = 'ALL';
        }

        $income_hospcode = $request->input('income_hospcode', 'ALL');
        $targetIncomeHosps = ($income_hospcode !== 'ALL' && in_array($income_hospcode, $allHospCodes)) 
            ? [$income_hospcode] 
            : $communityCodes;

        $incomeMonthsList = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $year_short = (int)substr($budget_year, -2);
        $prev_year_short = $year_short - 1;
        $incomeMonthsNames = [
            10 => "ต.ค. {$prev_year_short}",
            11 => "พ.ย. {$prev_year_short}",
            12 => "ธ.ค. {$prev_year_short}",
            1  => "ม.ค. {$year_short}",
            2  => "ก.พ. {$year_short}",
            3  => "มี.ค. {$year_short}",
            4  => "เม.ย. {$year_short}",
            5  => "พ.ค. {$year_short}",
            6  => "มิ.ย. {$year_short}",
            7  => "ก.ค. {$year_short}",
            8  => "ส.ค. {$year_short}",
            9  => "ก.ย. {$year_short}"
        ];

        // Query Income Categories for the whole Fiscal Year (IPD ใช้ยอดไม่นับซ้ำราย AN และกรอง รพ.ต้นสังกัด)
        $buildTab4IncomeQuery = function () use ($income_hospcode, $hubHospitalCode, $income_service_type, $targetIncomeHosps, $fiscal_start, $fiscal_end) {
            if ($income_hospcode === $hubHospitalCode) {
                $q = DB::table('opoh_income_categories')
                    ->whereBetween('vstdate', [$fiscal_start, $fiscal_end])
                    ->where('hospcode', $hubHospitalCode);
                if ($income_service_type !== 'ALL') {
                    $q->where('service_type', $income_service_type);
                }
                return $q;
            }
            return $this->incomeCategoriesQuery($income_service_type, $targetIncomeHosps, $fiscal_start, $fiscal_end);
        };

        $rawIncomeCats = $buildTab4IncomeQuery()->selectRaw("
            MONTH(vstdate) as month_num,
            drg_chrgitem_id,
            COALESCE(SUM(inc_ucs_incup), 0)   AS ucs_incup,
            COALESCE(SUM(inc_ucs_outprov), 0) AS ucs_outprov,
            COALESCE(SUM(inc_ofc), 0)         AS ofc,
            COALESCE(SUM(inc_lgo), 0)         AS lgo,
            COALESCE(SUM(inc_sss), 0)         AS sss,
            COALESCE(SUM(inc_other), 0)       AS other,
            COALESCE(SUM(inc_total), 0)       AS total
        ")
        ->groupBy(DB::raw("MONTH(vstdate)"), 'drg_chrgitem_id')
        ->get();

        // Breakdown รายโรงพยาบาล: $incomeByHosp[period('yearly'|เดือน)][catId][hospcode] = [uIn,uOut,ofc,lgo,sss,other,total]
        $rawIncomeByHosp = $buildTab4IncomeQuery()->selectRaw("
            hospcode,
            MONTH(vstdate) as month_num,
            drg_chrgitem_id,
            COALESCE(SUM(inc_ucs_incup), 0)   AS ucs_incup,
            COALESCE(SUM(inc_ucs_outprov), 0) AS ucs_outprov,
            COALESCE(SUM(inc_ofc), 0)         AS ofc,
            COALESCE(SUM(inc_lgo), 0)         AS lgo,
            COALESCE(SUM(inc_sss), 0)         AS sss,
            COALESCE(SUM(inc_other), 0)       AS other,
            COALESCE(SUM(inc_total), 0)       AS total
        ")
        ->groupBy('hospcode', DB::raw("MONTH(vstdate)"), 'drg_chrgitem_id')
        ->get();

        $incomeByHosp = [];
        foreach ($rawIncomeByHosp as $r) {
            $vals = [
                (float)$r->ucs_incup, (float)$r->ucs_outprov, (float)$r->ofc,
                (float)$r->lgo, (float)$r->sss, (float)$r->other, (float)$r->total,
            ];
            foreach (['yearly', (int)$r->month_num] as $period) {
                $cur = $incomeByHosp[$period][(int)$r->drg_chrgitem_id][$r->hospcode] ?? array_fill(0, 7, 0.0);
                foreach ($vals as $i => $v) {
                    $cur[$i] += $v;
                }
                $incomeByHosp[$period][(int)$r->drg_chrgitem_id][$r->hospcode] = $cur;
            }
        }

        $incomeYearlyData = [];
        foreach ($masterCategories as $catId => $catName) {
            $incomeYearlyData[$catId] = (object)[
                'group_id'    => $catId,
                'group_name'  => $catName,
                'ucs_incup'   => 0.0,
                'ucs_outprov' => 0.0,
                'ofc'         => 0.0,
                'lgo'         => 0.0,
                'sss'         => 0.0,
                'other'       => 0.0,
                'total'       => 0.0,
            ];
        }

        $incomeMonthlyData = [];
        $incomeSumsMonthly = [];
        foreach ($incomeMonthsList as $m) {
            $incomeMonthlyData[$m] = [];
            foreach ($masterCategories as $catId => $catName) {
                $incomeMonthlyData[$m][$catId] = (object)[
                    'group_id'    => $catId,
                    'group_name'  => $catName,
                    'ucs_incup'   => 0.0,
                    'ucs_outprov' => 0.0,
                    'ofc'         => 0.0,
                    'lgo'         => 0.0,
                    'sss'         => 0.0,
                    'other'       => 0.0,
                    'total'       => 0.0,
                ];
            }
            $incomeSumsMonthly[$m] = [
                'ucs_incup'   => 0.0,
                'ucs_outprov' => 0.0,
                'ofc'         => 0.0,
                'lgo'         => 0.0,
                'sss'         => 0.0,
                'other'       => 0.0,
                'total'       => 0.0,
            ];
        }

        $incomeSumsYearly = [
            'ucs_incup'   => 0.0,
            'ucs_outprov' => 0.0,
            'ofc'         => 0.0,
            'lgo'         => 0.0,
            'sss'         => 0.0,
            'other'       => 0.0,
            'total'       => 0.0,
        ];

        foreach ($rawIncomeCats as $row) {
            $m = (int)$row->month_num;
            $catId = (int)$row->drg_chrgitem_id;

            if (!isset($incomeYearlyData[$catId])) {
                $cName = $catId == 17 ? '17. ยาอื่นๆ ที่ไม่ใช่หมวด 3 และ 4' : ($catId == 19 ? '19. ค่าบริการอื่นๆ' : "หมวด {$catId}");
                $incomeYearlyData[$catId] = (object)[
                    'group_id'    => $catId,
                    'group_name'  => $cName,
                    'ucs_incup'   => 0.0,
                    'ucs_outprov' => 0.0,
                    'ofc'         => 0.0,
                    'lgo'         => 0.0,
                    'sss'         => 0.0,
                    'other'       => 0.0,
                    'total'       => 0.0,
                ];
                foreach ($incomeMonthsList as $mi) {
                    $incomeMonthlyData[$mi][$catId] = (object)[
                        'group_id'    => $catId,
                        'group_name'  => $cName,
                        'ucs_incup'   => 0.0,
                        'ucs_outprov' => 0.0,
                        'ofc'         => 0.0,
                        'lgo'         => 0.0,
                        'sss'         => 0.0,
                        'other'       => 0.0,
                        'total'       => 0.0,
                    ];
                }
            }

            $uIn  = (float)$row->ucs_incup;
            $uOut = (float)$row->ucs_outprov;
            $ofc  = (float)$row->ofc;
            $lgo  = (float)$row->lgo;
            $sss  = (float)$row->sss;
            $oth  = (float)$row->other;
            $tot  = (float)$row->total;

            if (isset($incomeMonthlyData[$m][$catId])) {
                $incomeMonthlyData[$m][$catId]->ucs_incup   += $uIn;
                $incomeMonthlyData[$m][$catId]->ucs_outprov += $uOut;
                $incomeMonthlyData[$m][$catId]->ofc         += $ofc;
                $incomeMonthlyData[$m][$catId]->lgo         += $lgo;
                $incomeMonthlyData[$m][$catId]->sss         += $sss;
                $incomeMonthlyData[$m][$catId]->other       += $oth;
                $incomeMonthlyData[$m][$catId]->total       += $tot;

                $incomeSumsMonthly[$m]['ucs_incup']   += $uIn;
                $incomeSumsMonthly[$m]['ucs_outprov'] += $uOut;
                $incomeSumsMonthly[$m]['ofc']         += $ofc;
                $incomeSumsMonthly[$m]['lgo']         += $lgo;
                $incomeSumsMonthly[$m]['sss']         += $sss;
                $incomeSumsMonthly[$m]['other']       += $oth;
                $incomeSumsMonthly[$m]['total']       += $tot;
            }

            $incomeYearlyData[$catId]->ucs_incup   += $uIn;
            $incomeYearlyData[$catId]->ucs_outprov += $uOut;
            $incomeYearlyData[$catId]->ofc         += $ofc;
            $incomeYearlyData[$catId]->lgo         += $lgo;
            $incomeYearlyData[$catId]->sss         += $sss;
            $incomeYearlyData[$catId]->other       += $oth;
            $incomeYearlyData[$catId]->total       += $tot;

            $incomeSumsYearly['ucs_incup']   += $uIn;
            $incomeSumsYearly['ucs_outprov'] += $uOut;
            $incomeSumsYearly['ofc']         += $ofc;
            $incomeSumsYearly['lgo']         += $lgo;
            $incomeSumsYearly['sss']         += $sss;
            $incomeSumsYearly['other']       += $oth;
            $incomeSumsYearly['total']       += $tot;
        }

        // Monthly Trend Line Chart Data for ApexCharts
        $incomeChartData = [];
        $incomeChartData['all'] = [];
        foreach ($incomeMonthsList as $m) {
            $incomeChartData['all'][] = round($incomeSumsMonthly[$m]['total'] ?? 0.0, 2);
        }
        foreach ($incomeYearlyData as $catId => $cat) {
            $incomeChartData[$catId] = [];
            foreach ($incomeMonthsList as $m) {
                $val = $incomeMonthlyData[$m][$catId]->total ?? 0.0;
                $incomeChartData[$catId][] = round($val, 2);
            }
        }

        // Summary Cards for Income Tab (ไม่นับซ้ำราย AN และกรอง รพ.ต้นสังกัด)
        $incomeCumulativeGrandTotal = (float)$this->incomeCategoriesQuery('ALL', $communityCodes, $cumulative_start, $cumulative_end)
            ->sum('inc_total');

        $incomePeriodGrandTotal = (float)$this->incomeCategoriesQuery('ALL', $communityCodes, $current_start, $current_end)
            ->sum('inc_total');

        return view('dashboard_opoh', compact(
            'active_tab',
            'budget_year_select',
            'budget_year',
            'fiscal_start',
            'fiscal_end',
            'current_start',
            'current_end',
            'filter_type',
            'thaiDateRangeText',
            'specialties',
            'specialtyStats',
            'specialtyRows',
            'specColTotals',
            'hospitalsList',
            'hubHospitalCode',
            'hubHospitalName',
            'sumPast',
            'sumCurrent',
            'sumCumulative',
            'sumTelemed',
            'sumOnsite',
            'summary',
            'fiscalSummary',
            'ipdSpecialtyStats',
            'ipdSpecialtyRows',
            'ipdSpecColTotals',
            'ipdSumPast',
            'ipdSumCurrent',
            'ipdSumCumulative',
            'ipdSumLos',
            'ipdSumRw',
            'ipdSumInc',
            'opdMonthlyChart',
            'ipdMonthlyChart',
            'opSummary',
            'opCumSummary',
            'opSumCumulative',
            'topOpdDiagnoses',
            'topIpdDiagnoses',
            'topOperations',
            'opMonthlyChart',
            'surgicalSpecialties',
            'opSpecialtyRows',
            'opSpecColTotals',
            'rightsConfig',
            'opdRightsRows',
            'opdVisitsTotal',
            'opdIncTotal',
            'ipdRightsRows',
            'ipdAnsTotal',
            'ipdLosTotal',
            'ipdRwTotal',
            'ipdIncTotal',
            'opRightsRows',
            'opCasesTotal',
            'opIncTotal',
            'opRwTotal',
            'allModalHospitals',
            'opdRightsByHosp',
            'ipdRightsByHosp',
            'opRightsByHosp',
            'income_service_type',
            'income_hospcode',
            'masterCategories',
            'incomeMonthsList',
            'incomeMonthsNames',
            'incomeYearlyData',
            'incomeMonthlyData',
            'incomeChartData',
            'incomeByHosp',
            'incomeSumsYearly',
            'incomeSumsMonthly',
            'incomeCumulativeGrandTotal',
            'incomePeriodGrandTotal'
        ));
    }

    /**
     * Display OPOH Management Page (Admin / Authorized User only: allow_opoh)
     */
    public function manageIndex(Request $request)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        // 1. Fiscal Year Calculation
        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(5)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        $year_info = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        $fiscal_start = $year_info->DATE_BEGIN ?? Carbon::now()->startOfYear()->toDateString();
        $fiscal_end   = $year_info->DATE_END ?? Carbon::now()->endOfYear()->toDateString();

        // 2. Date Filtering
        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        $endOfWeek   = Carbon::now()->endOfWeek()->toDateString();

        $filter_type = $request->input('filter_type', 'week');
        if ($filter_type === 'fiscal') {
            $current_start = $fiscal_start;
            $current_end   = $fiscal_end;
        } elseif ($filter_type === 'month') {
            $current_start = Carbon::now()->startOfMonth()->toDateString();
            $current_end   = Carbon::now()->endOfMonth()->toDateString();
        } elseif ($filter_type === 'custom') {
            $current_start = $request->input('start_date', $startOfWeek);
            $current_end   = $request->input('end_date', $endOfWeek);
        } else {
            $current_start = $request->input('start_date', $startOfWeek);
            $current_end   = $request->input('end_date', $endOfWeek);
        }

        $spclty_filter = $request->input('spclty_filter', '');

        // 3. Specialties & Hospitals Definition
        $specialties = [
            '01' => ['name' => 'อายุรกรรม', 'color' => '#0d6efd', 'icon' => 'fa-stethoscope'],
            '02' => ['name' => 'ศัลยกรรมทั่วไป', 'color' => '#6f42c1', 'icon' => 'fa-syringe'],
            '06' => ['name' => 'ศัลยกรรมกระดูก', 'color' => '#d63384', 'icon' => 'fa-bone'],
            '03' => ['name' => 'สูตินรีเวชกรรม', 'color' => '#fd7e14', 'icon' => 'fa-person-pregnant'],
            '05' => ['name' => 'กุมารเวชกรรม', 'color' => '#198754', 'icon' => 'fa-baby'],
            '07' => ['name' => 'จิตเวช', 'color' => '#20c997', 'icon' => 'fa-brain'],
            '08' => ['name' => 'จักษุ', 'color' => '#0dcaf0', 'icon' => 'fa-eye'],
        ];

        // โรงพยาบาล รพช. และ รพ.แม่ข่าย แบบ dynamic รองรับทุกจังหวัด
        $hospitalsMeta   = $this->resolveOpohHospitals();
        $hospitalsList   = $hospitalsMeta['community'];
        $communityCodes  = $hospitalsMeta['community_codes'];
        $hubHospitalCode = $hospitalsMeta['hub_code'];
        $hubHospitalName = $hospitalsMeta['hub_name'];
        $originHospitals = $hospitalsMeta['selectable_origins'];
        $allHospCodes    = array_keys($hospitalsMeta['modal_hospitals']);

        $cumulative_start = '2025-10-01'; // 1 ต.ค. 2568

        // 4. Query Doctor Visit Statistics
        $doctorQuery = OpohDoctor::with('originHospital')->orderBy('spclty_code')->orderBy('id');
        if (!empty($spclty_filter)) {
            $doctorQuery->where('spclty_code', $spclty_filter);
        }
        $doctors = $doctorQuery->get();

        $allDoctors = OpohDoctor::with('originHospital')->orderBy('spclty_code')->orderBy('id')->get();
        $totalDoctors = $allDoctors->count();
        $activeDoctors = $allDoctors->where('is_active', 'Y')->count();

        // 4.1 Cumulative visits per doctor (1 ต.ค. 68 - $current_end) เฉพาะ รพช. ไม่นับ รพ.ต้นสังกัดของแพทย์
        $docCumulativeVisitsQ = DB::table('opoh_opd_service as s')
            ->where('s.vstdate', '>=', $cumulative_start)
            ->where('s.vstdate', '<=', $current_end)
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docCumulativeVisitsQ, 's');
        $docCumulativeVisits = $docCumulativeVisitsQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.visit_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // 4.2 Current period visits per doctor ($current_start - $current_end) เฉพาะ รพช. ไม่นับ รพ.ต้นสังกัดของแพทย์
        $docPeriodVisitsQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docPeriodVisitsQ, 's');
        $docPeriodVisits = $docPeriodVisitsQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.visit_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // 4.3 Hub Hospital visits per doctor (ตรวจ ณ รพ.แม่ข่าย ช่วงที่เลือก)
        $docAmnatPeriodVisits = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'doctor_id')
            ->toArray();

        // 4.4 Current period by doctor and hospcode
        $docHospMatrixRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id', 'hospcode')
            ->selectRaw('doctor_id, hospcode, COALESCE(SUM(visit_total), 0) as visits')
            ->get();

        $docHospMatrix = [];
        foreach ($docHospMatrixRaw as $m) {
            $docHospMatrix[$m->doctor_id][$m->hospcode] = (int)$m->visits;
        }

        // 4.4.1 OPD Doctor Income from opoh_income_categories (กรอง รพ.ต้นสังกัด)
        $docPeriodOpdIncQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'OPD')
            ->whereBetween('ic.vstdate', [$current_start, $current_end])
            ->whereIn('ic.hospcode', $communityCodes)
            ->whereNotNull('ic.doctor_id');
        $this->applyOriginExclusion($docPeriodOpdIncQ, 'ic');
        $docPeriodOpdInc = $docPeriodOpdIncQ
            ->groupBy('ic.doctor_id')
            ->selectRaw('ic.doctor_id as k, COALESCE(SUM(ic.inc_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // Build Doctor Service Stats Rows
        $docStats = [];
        $colTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'inc'        => 0.0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $colTotals[$hcode] = 0;
        }

        foreach ($doctors as $doc) {
            $cumul = (int)($docCumulativeVisits[$doc->id] ?? 0);
            $period = (int)($docPeriodVisits[$doc->id] ?? 0);
            $amnatVisits = (int)($docAmnatPeriodVisits[$doc->id] ?? 0);
            $opdInc = (float)($docPeriodOpdInc[$doc->id] ?? 0.0);

            $colTotals['cumulative'] += $cumul;
            $colTotals['period'] += $period;
            $colTotals['inc'] += $opdInc;
            $colTotals['amnat'] += $amnatVisits;

            $hospVisits = [];
            foreach ($hospitalsList as $hcode => $hname) {
                $cnt = (int)($docHospMatrix[$doc->id][$hcode] ?? 0);
                $hospVisits[$hcode] = $cnt;
                // ไม่นำยอดตรวจ ณ รพ.ต้นสังกัดของแพทย์มารวมในยอดรวมของ รพ.ปลายทาง
                if ((string)$doc->origin_hospcode !== (string)$hcode) {
                    $colTotals[$hcode] += $cnt;
                }
            }

            $docStats[] = [
                'doctor'       => $doc,
                'cumulative'   => $cumul,
                'period'       => $period,
                'inc'          => $opdInc,
                'hosp_visits'  => $hospVisits,
                'amnat_visits' => $amnatVisits,
            ];
        }

        // 4.4.2 Grand Total for OPD across Community Hospitals (กรอง รพ.ต้นสังกัดออกเช่นเดียวกับหน้า Dashboard)
        $grandOpdCumulativeQ = DB::table('opoh_opd_service as s')
            ->where('s.vstdate', '>=', $cumulative_start)
            ->where('s.vstdate', '<=', $current_end)
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($grandOpdCumulativeQ, 's');
        $grandOpdCumulative = (int)$grandOpdCumulativeQ->sum('s.visit_total');

        $grandOpdPeriodQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($grandOpdPeriodQ, 's');
        $grandOpdPeriod = (int)$grandOpdPeriodQ->sum('s.visit_total');

        $grandOpdHospVisitsQ = DB::table('opoh_opd_service as s')
            ->whereBetween('s.vstdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes);
        $this->applyOriginExclusion($grandOpdHospVisitsQ, 's');
        $grandOpdHospVisitsRaw = $grandOpdHospVisitsQ
            ->groupBy('s.hospcode')
            ->selectRaw('s.hospcode as k, COALESCE(SUM(s.visit_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $grandOpdInc = (float)$this->incomeCategoriesQuery('OPD', $communityCodes, $current_start, $current_end)
            ->sum('inc_total');

        $grandOpdAmnatVisits = (int)DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->sum('visit_total');

        $grandOpdTotals = [
            'cumulative' => $grandOpdCumulative,
            'period'     => $grandOpdPeriod,
            'inc'        => $grandOpdInc,
            'amnat'      => $grandOpdAmnatVisits,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $grandOpdTotals[$hcode] = (int)($grandOpdHospVisitsRaw[$hcode] ?? 0);
        }

        // 4.5 IPD Doctor Visit Statistics (กรอง รพ.ต้นสังกัดของแพทย์ออก)
        $docCumulativeIpdQ = DB::table('opoh_ipd_service as s')
            ->where('s.dchdate', '>=', $cumulative_start)
            ->where('s.dchdate', '<=', $current_end)
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docCumulativeIpdQ, 's');
        $docCumulativeIpd = $docCumulativeIpdQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.an_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $docPeriodIpdQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docPeriodIpdQ, 's');
        $docPeriodIpd = $docPeriodIpdQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.an_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $docPeriodLosQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docPeriodLosQ, 's');
        $docPeriodLos = $docPeriodLosQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.admdate_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $docPeriodRwQ = DB::table('opoh_ipd_service as s')
            ->whereBetween('s.dchdate', [$current_start, $current_end])
            ->whereIn('s.hospcode', $communityCodes)
            ->whereNotNull('s.doctor_id');
        $this->applyOriginExclusion($docPeriodRwQ, 's');
        $docPeriodRw = $docPeriodRwQ
            ->groupBy('s.doctor_id')
            ->selectRaw('s.doctor_id as k, COALESCE(SUM(s.adjrw_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        $docPeriodIncQ = DB::table('opoh_income_categories as ic')
            ->where('ic.service_type', 'IPD')
            ->whereBetween('ic.vstdate', [$current_start, $current_end])
            ->whereIn('ic.hospcode', $communityCodes)
            ->whereNotNull('ic.doctor_id');
        $this->applyOriginExclusion($docPeriodIncQ, 'ic');
        $docPeriodInc = $docPeriodIncQ
            ->groupBy('ic.doctor_id')
            ->selectRaw('ic.doctor_id as k, COALESCE(SUM(ic.inc_total), 0) as v')
            ->pluck('v', 'k')
            ->toArray();

        // Hub Hospital IPD visits per doctor (ตรวจ ณ รพ.แม่ข่าย ช่วงที่เลือก)
        $docAmnatPeriodIpd = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'doctor_id')
            ->toArray();

        $docHospIpdMatrixRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id', 'hospcode')
            ->selectRaw('doctor_id, hospcode, COALESCE(SUM(an_total), 0) as ans')
            ->get();

        $docHospIpdMatrix = [];
        foreach ($docHospIpdMatrixRaw as $m) {
            $docHospIpdMatrix[$m->doctor_id][$m->hospcode] = (int)$m->ans;
        }

        $docIpdStats = [];
        $colIpdTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'los'        => 0,
            'rw'         => 0.0,
            'inc'        => 0.0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $colIpdTotals[$hcode] = 0;
        }

        foreach ($doctors as $doc) {
            $cumul = (int)($docCumulativeIpd[$doc->id] ?? 0);
            $period = (int)($docPeriodIpd[$doc->id] ?? 0);
            $los = (int)($docPeriodLos[$doc->id] ?? 0);
            $rw = (float)($docPeriodRw[$doc->id] ?? 0.0);
            $inc = (float)($docPeriodInc[$doc->id] ?? 0.0);
            $amnatAns = (int)($docAmnatPeriodIpd[$doc->id] ?? 0);

            $colIpdTotals['cumulative'] += $cumul;
            $colIpdTotals['period'] += $period;
            $colIpdTotals['los'] += $los;
            $colIpdTotals['rw'] += $rw;
            $colIpdTotals['inc'] += $inc;
            $colIpdTotals['amnat'] += $amnatAns;

            $hospAns = [];
            foreach ($hospitalsList as $hcode => $hname) {
                $cnt = (int)($docHospIpdMatrix[$doc->id][$hcode] ?? 0);
                $hospAns[$hcode] = $cnt;
                if ((string)$doc->origin_hospcode !== (string)$hcode) {
                    $colIpdTotals[$hcode] += $cnt;
                }
            }

            $docIpdStats[] = [
                'doctor'       => $doc,
                'cumulative'   => $cumul,
                'period'       => $period,
                'los'          => $los,
                'rw'           => $rw,
                'inc'          => $inc,
                'hosp_ans'     => $hospAns,
                'amnat_ans'    => $amnatAns,
            ];
        }

        // 4.5.2 Grand Total for IPD across Community Hospitals (ไม่นับซ้ำราย AN และกรอง รพ.ต้นสังกัด)
        $grandIpdCumRow = $this->ipdHospitalRowsQuery($communityCodes, $cumulative_start, $current_end)
            ->selectRaw('COALESCE(SUM(an_total), 0) AS an_total')
            ->first();
        $grandIpdPeriodRow = $this->ipdHospitalRowsQuery($communityCodes, $current_start, $current_end)
            ->selectRaw('COALESCE(SUM(an_total), 0) AS an_total, COALESCE(SUM(admdate_total), 0) AS los_total, COALESCE(SUM(adjrw_total), 0) AS adjrw_total')
            ->first();

        $grandIpdCumulative = (int)($grandIpdCumRow->an_total ?? 0);
        $grandIpdPeriod     = (int)($grandIpdPeriodRow->an_total ?? 0);
        $grandIpdLos        = (int)($grandIpdPeriodRow->los_total ?? 0);
        $grandIpdRw         = (float)($grandIpdPeriodRow->adjrw_total ?? 0.0);

        $grandIpdHospAnsRaw = $this->ipdHospitalRowsQuery($communityCodes, $current_start, $current_end)
            ->groupBy('hospcode')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'hospcode')
            ->toArray();

        $grandIpdInc = (float)$this->incomeCategoriesQuery('IPD', $communityCodes, $current_start, $current_end)
            ->sum('inc_total');

        $grandIpdAmnatAns = (int)DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->where('hospcode', $hubHospitalCode)
            ->sum('an_total');

        $grandIpdTotals = [
            'cumulative' => $grandIpdCumulative,
            'period'     => $grandIpdPeriod,
            'los'        => $grandIpdLos,
            'rw'         => $grandIpdRw,
            'inc'        => $grandIpdInc,
            'amnat'      => $grandIpdAmnatAns,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $grandIpdTotals[$hcode] = (int)($grandIpdHospAnsRaw[$hcode] ?? 0);
        }

        $formatThaiDate = function($dateStr, $full = false) {
            if (!$dateStr) return '';
            $d = Carbon::parse($dateStr)->locale('th');
            $yearBe = $d->year + 543;
            $format = $full ? 'D MMMM' : 'D MMM';
            return $d->isoFormat($format) . ' ' . $yearBe;
        };

        $thaiDateRangeText = $formatThaiDate($current_start) . ' ถึง ' . $formatThaiDate($current_end);

        return view('admin.opoh', compact(
            'docStats',
            'colTotals',
            'grandOpdTotals',
            'docIpdStats',
            'colIpdTotals',
            'grandIpdTotals',
            'hospitalsList',
            'hubHospitalCode',
            'hubHospitalName',
            'originHospitals',
            'specialties',
            'budget_year_select',
            'budget_year',
            'fiscal_start',
            'fiscal_end',
            'current_start',
            'current_end',
            'filter_type',
            'thaiDateRangeText',
            'spclty_filter',
            'allDoctors',
            'totalDoctors',
            'activeDoctors'
        ));
    }

    /**
     * Store a new doctor in OPOH registry
     */
    public function storeDoctor(Request $request)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $specMap = [
            '01' => 'อายุรกรรม',
            '02' => 'ศัลยกรรมทั่วไป',
            '06' => 'ศัลยกรรมกระดูก',
            '03' => 'สูตินรีเวชกรรม',
            '05' => 'กุมารเวชกรรม',
            '07' => 'จิตเวช',
            '08' => 'จักษุ',
        ];

        $validated = $request->validate([
            'pname'           => ['nullable', 'string', 'max:50'],
            'fname'           => ['required', 'string', 'max:100'],
            'lname'           => ['required', 'string', 'max:100'],
            'cid'             => ['nullable', 'string', 'max:13'],
            'licenseno'       => ['nullable', 'string', 'max:20'],
            'spclty_code'     => ['required', 'string', 'max:10'],
            'specialty_name'  => ['nullable', 'string', 'max:100'],
            'position'        => ['nullable', 'string', 'max:150'],
            'origin_hospcode' => ['nullable', 'string', 'max:10'],
            'is_active'       => ['nullable'],
        ]);

        $specialty_name = !empty($validated['specialty_name']) ? $validated['specialty_name'] : ($specMap[$validated['spclty_code']] ?? 'อายุรกรรม');
        $doctor_name = trim(($validated['pname'] ?? '') . ' ' . $validated['fname'] . ' ' . $validated['lname']);
        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['Y', '1', 'on']) ? 'Y' : 'N';
        $originHospcode = !empty(trim((string)($validated['origin_hospcode'] ?? ''))) ? trim((string)$validated['origin_hospcode']) : null;

        $doctor = OpohDoctor::create([
            'pname'           => $validated['pname'] ?? null,
            'fname'           => $validated['fname'],
            'lname'           => $validated['lname'],
            'doctor_name'     => $doctor_name,
            'cid'             => !empty($validated['cid']) ? preg_replace('/[^0-9]/', '', $validated['cid']) : null,
            'licenseno'       => !empty($validated['licenseno']) ? trim($validated['licenseno']) : null,
            'spclty_code'     => $validated['spclty_code'],
            'specialty_name'  => $specialty_name,
            'position'        => $validated['position'] ?? null,
            'origin_hospcode' => $originHospcode,
            'is_active'       => $isActive,
            'created_by'      => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'เพิ่มแพทย์เรียบร้อยแล้ว',
            'doctor'  => $doctor,
        ]);
    }

    /**
     * Update doctor information
     */
    public function updateDoctor(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $doctor = OpohDoctor::findOrFail($id);

        $specMap = [
            '01' => 'อายุรกรรม',
            '02' => 'ศัลยกรรมทั่วไป',
            '06' => 'ศัลยกรรมกระดูก',
            '03' => 'สูตินรีเวชกรรม',
            '05' => 'กุมารเวชกรรม',
            '07' => 'จิตเวช',
            '08' => 'จักษุ',
        ];

        $validated = $request->validate([
            'pname'           => ['nullable', 'string', 'max:50'],
            'fname'           => ['required', 'string', 'max:100'],
            'lname'           => ['required', 'string', 'max:100'],
            'cid'             => ['nullable', 'string', 'max:13'],
            'licenseno'       => ['nullable', 'string', 'max:20'],
            'spclty_code'     => ['required', 'string', 'max:10'],
            'specialty_name'  => ['nullable', 'string', 'max:100'],
            'position'        => ['nullable', 'string', 'max:150'],
            'origin_hospcode' => ['nullable', 'string', 'max:10'],
            'is_active'       => ['nullable'],
        ]);

        $specialty_name = !empty($validated['specialty_name']) ? $validated['specialty_name'] : ($specMap[$validated['spclty_code']] ?? $doctor->specialty_name);
        $doctor_name = trim(($validated['pname'] ?? '') . ' ' . $validated['fname'] . ' ' . $validated['lname']);
        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['Y', '1', 'on']) ? 'Y' : 'N';
        $originHospcode = !empty(trim((string)($validated['origin_hospcode'] ?? ''))) ? trim((string)$validated['origin_hospcode']) : null;

        $doctor->update([
            'pname'           => $validated['pname'] ?? null,
            'fname'           => $validated['fname'],
            'lname'           => $validated['lname'],
            'doctor_name'     => $doctor_name,
            'cid'             => !empty($validated['cid']) ? preg_replace('/[^0-9]/', '', $validated['cid']) : null,
            'licenseno'       => !empty($validated['licenseno']) ? trim($validated['licenseno']) : null,
            'spclty_code'     => $validated['spclty_code'],
            'specialty_name'  => $specialty_name,
            'position'        => $validated['position'] ?? null,
            'origin_hospcode' => $originHospcode,
            'is_active'       => $isActive,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขข้อมูลแพทย์เรียบร้อยแล้ว',
            'doctor'  => $doctor,
        ]);
    }

    /**
     * Delete doctor
     */
    public function deleteDoctor($id)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $doctor = OpohDoctor::findOrFail($id);
        $doctor->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบข้อมูลแพทย์เรียบร้อยแล้ว',
        ]);
    }

    /**
     * Toggle doctor active status
     */
    public function toggleDoctorStatus($id)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $doctor = OpohDoctor::findOrFail($id);
        $doctor->is_active = $doctor->is_active === 'Y' ? 'N' : 'Y';
        $doctor->save();

        return response()->json([
            'success'   => true,
            'is_active' => $doctor->is_active,
            'message'   => 'เปลี่ยนสถานะแพทย์เรียบร้อยแล้ว',
        ]);
    }

    /**
     * Get JSON list of doctors for modal live reloading
     */
    public function getDoctorsList()
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $specialties = [
            '01' => ['name' => 'อายุรกรรม', 'color' => '#0284c7', 'icon' => 'fa-stethoscope'],
            '02' => ['name' => 'ศัลยกรรมทั่วไป', 'color' => '#6f42c1', 'icon' => 'fa-syringe'],
            '06' => ['name' => 'ศัลยกรรมกระดูก', 'color' => '#d63384', 'icon' => 'fa-bone'],
            '03' => ['name' => 'สูตินรีเวชกรรม', 'color' => '#fd7e14', 'icon' => 'fa-person-pregnant'],
            '05' => ['name' => 'กุมารเวชกรรม', 'color' => '#198754', 'icon' => 'fa-baby'],
            '07' => ['name' => 'จิตเวช', 'color' => '#20c997', 'icon' => 'fa-brain'],
            '08' => ['name' => 'จักษุ', 'color' => '#0dcaf0', 'icon' => 'fa-eye'],
        ];

        $hospitalsMeta = $this->resolveOpohHospitals();
        $originNames = $hospitalsMeta['selectable_origins'];

        $doctors = OpohDoctor::with('originHospital')->orderBy('spclty_code')->orderBy('id')->get()->map(function($doc) use ($specialties, $originNames) {
            $specColor = $specialties[$doc->spclty_code]['color'] ?? '#0284c7';
            $specIcon = $specialties[$doc->spclty_code]['icon'] ?? 'fa-user-doctor';
            $originCode = $doc->origin_hospcode ?: null;
            $originName = null;
            if ($originCode) {
                $originName = $originNames[$originCode] ?? ($doc->originHospital ? str_replace('โรงพยาบาล', 'รพ.', $doc->originHospital->name) : $originCode);
            }
            return [
                'id'                   => $doc->id,
                'pname'                => $doc->pname,
                'fname'                => $doc->fname,
                'lname'                => $doc->lname,
                'doctor_name'          => $doc->doctor_name,
                'spclty_code'          => $doc->spclty_code,
                'specialty_name'       => $doc->specialty_name,
                'licenseno'            => $doc->licenseno,
                'cid'                  => $doc->cid,
                'position'             => $doc->position,
                'origin_hospcode'      => $originCode,
                'origin_hospital_name' => $originName,
                'is_active'            => $doc->is_active,
                'spec_color'           => $specColor,
                'spec_icon'            => $specIcon,
            ];
        });

        return response()->json([
            'success' => true,
            'doctors' => $doctors,
            'total'   => $doctors->count(),
            'active'  => $doctors->where('is_active', 'Y')->count(),
        ]);
    }

    /**
     * ตรวจสิทธิ์ + ตรวจพารามิเตอร์ของรายเคสแพทย์ (ใช้ร่วมกันทั้ง modal และ export)
     * คืน [array $ctx, null] เมื่อผ่าน หรือ [null, JsonResponse] เมื่อไม่ผ่าน
     */
    private function resolveDoctorRecordsContext(Request $request): array
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return [null, response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ดูข้อมูลรายเคส (ต้องมีสิทธิ์ allow_opoh)'], 403)];
        }

        $hospitalsMeta  = $this->resolveOpohHospitals();
        $communityCodes = $hospitalsMeta['community_codes'];
        $type     = $request->input('type');
        $hospcode = (string)$request->input('hospcode');
        $start    = (string)$request->input('start_date');
        $end      = (string)$request->input('end_date');

        if (!in_array($type, ['opd', 'ipd'], true)) {
            return [null, response()->json(['success' => false, 'message' => 'ประเภทบริการไม่ถูกต้อง'], 422)];
        }
        if (!in_array($hospcode, $communityCodes, true)) {
            return [null, response()->json(['success' => false, 'message' => 'รองรับเฉพาะ รพช. ในเครือข่าย OPOH'], 422)];
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            return [null, response()->json(['success' => false, 'message' => 'ช่วงวันที่ไม่ถูกต้อง'], 422)];
        }

        // ดึงเลขบัตร/เลข ว. จากฐานข้อมูลเอง ไม่เชื่อค่าจากฝั่ง client
        $doctor = DB::table('opoh_doctors')->where('id', (int)$request->input('doctor_id'))->first();
        if (!$doctor) {
            return [null, response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลแพทย์'], 404)];
        }
        if (!empty($doctor->origin_hospcode) && (string)$doctor->origin_hospcode === $hospcode) {
            return [null, response()->json(['success' => false, 'message' => 'ไม่แสดงรายเคสของแพทย์ ณ โรงพยาบาลต้นสังกัด (ไม่นับเป็นยอด OPOH)'], 422)];
        }
        if (empty(trim((string)$doctor->cid)) && empty(trim((string)$doctor->licenseno))) {
            return [null, response()->json(['success' => false, 'message' => 'แพทย์ท่านนี้ไม่มีเลขบัตรประชาชนหรือเลข ว. จึงจับคู่กับ HOSxP ไม่ได้'], 422)];
        }

        $hosp = Hospital::where('hospcode', $hospcode)->first();

        return [[
            'type'          => $type,
            'hospcode'      => $hospcode,
            'hospital_name' => $hosp->name ?? $hospcode,
            'start'         => $start,
            'end'           => $end,
            'doctor'        => $doctor,
        ], null];
    }

    /**
     * บันทึก audit ทุกครั้งที่เปิดดู/ส่งออกรายเคส (มีข้อมูลระบุตัวผู้ป่วย - PDPA)
     */
    private function auditDoctorRecords(Request $request, array $ctx, string $action, string $status, int $count, ?string $error = null): void
    {
        try {
            $u = Auth::user();
            \App\Models\AemrAccessLog::create([
                'user_id'          => $u->id,
                'username'         => $u->email ?? $u->name,
                'user_name'        => $u->name ?? 'User',
                'user_role'        => $u->role ?? 'staff',
                'user_hospcode'    => $u->hospcode ?? null,
                'provider_id'      => $u->provider_id ?? null,
                'action'           => $action,
                'target_hospcode'  => $ctx['hospcode'],
                'reason'           => mb_substr(sprintf(
                    'OPOH รายเคส %s แพทย์ %s (ว.%s) %s ถึง %s',
                    strtoupper($ctx['type']),
                    $ctx['doctor']->doctor_name,
                    $ctx['doctor']->licenseno ?: '-',
                    $ctx['start'],
                    $ctx['end']
                ), 0, 255),
                'ip_address'       => $request->ip() ?: '127.0.0.1',
                'user_agent'       => substr($request->userAgent() ?? '', 0, 500),
                'request_url'      => substr($request->fullUrl(), 0, 500),
                'http_method'      => $request->method(),
                'status'           => $status,
                'http_status_code' => 200,
                'response_time_ms' => 0,
                'record_count'     => $count,
                'error_message'    => $error,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to write OPOH records audit log: ' . $e->getMessage());
        }
    }

    /**
     * ยอดที่ server มีอยู่ (จากตารางสรุปที่ agent ส่งมา) ใช้เทียบกระทบยอดกับจำนวนเคสที่ได้จาก agent สด
     */
    private function expectedDoctorCases(array $ctx): int
    {
        if (!empty($ctx['doctor']->origin_hospcode) && (string)$ctx['doctor']->origin_hospcode === (string)$ctx['hospcode']) {
            return 0;
        }
        if ($ctx['type'] === 'opd') {
            return (int)DB::table('opoh_opd_service')
                ->where('doctor_id', $ctx['doctor']->id)
                ->where('hospcode', $ctx['hospcode'])
                ->whereBetween('vstdate', [$ctx['start'], $ctx['end']])
                ->sum('visit_total');
        }
        return (int)DB::table('opoh_ipd_service')
            ->where('doctor_id', $ctx['doctor']->id)
            ->where('hospcode', $ctx['hospcode'])
            ->whereBetween('dchdate', [$ctx['start'], $ctx['end']])
            ->sum('an_total');
    }

    /**
     * AJAX (DataTables server-side): รายเคสของแพทย์ 1 คน ใน รพ. 1 แห่ง ดึงสดผ่าน agent
     * เฉพาะผู้มีสิทธิ์ allow_opoh
     */
    public function getDoctorRecords(Request $request, \App\Services\OpohRecordsService $svc)
    {
        [$ctx, $err] = $this->resolveDoctorRecordsContext($request);
        if ($err) {
            return $err;
        }
        @set_time_limit(60);

        $length = (int)$request->input('length', 25);
        $length = ($length < 1 || $length > 500) ? 25 : $length;
        $orderIdx = $request->input('order.0.column');
        $orderCol = $orderIdx !== null ? $request->input("columns.{$orderIdx}.name") : null;
        $orderDir = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $wantSummary = $request->boolean('summary');

        $res = $svc->fetch($ctx['hospcode'], [
            'type'       => $ctx['type'],
            'cid'        => trim((string)$ctx['doctor']->cid),
            'licenseno'  => trim((string)$ctx['doctor']->licenseno),
            'start_date' => $ctx['start'],
            'end_date'   => $ctx['end'],
            'offset'     => max(0, (int)$request->input('start', 0)),
            'limit'      => $length,
            'search'     => (string)$request->input('search.value', ''),
            'order_col'  => is_string($orderCol) ? $orderCol : '',
            'order_dir'  => $orderDir,
            'summary'    => $wantSummary,
        ]);

        if (!$res['success']) {
            $this->auditDoctorRecords($request, $ctx, 'OPOH_DOCTOR_RECORDS', 'ERROR', 0, $res['message']);
            return response()->json([
                'draw' => (int)$request->input('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [],
                'success' => false, 'message' => $res['message'],
            ]);
        }

        $data = $res['data'] ?? [];
        $rows = $data['rows'] ?? [];
        $this->auditDoctorRecords($request, $ctx, 'OPOH_DOCTOR_RECORDS', 'SUCCESS', count($rows));

        $payload = [
            'success'         => true,
            'draw'            => (int)$request->input('draw'),
            'recordsTotal'    => (int)($data['records_total'] ?? 0),
            'recordsFiltered' => (int)($data['records_filtered'] ?? 0),
            'data'            => $rows,
        ];
        if ($wantSummary) {
            $payload['summary']        = $data['summary'] ?? null;
            $payload['expected_total'] = $this->expectedDoctorCases($ctx);
            $payload['doctor_name']    = $ctx['doctor']->doctor_name;
            $payload['hospital_name']  = $ctx['hospital_name'];
        }
        return response()->json($payload);
    }

    /**
     * ส่งออกรายเคส "ทุกเคส" (หลัง search ถ้ามี) เป็น CSV (เปิดด้วย Excel ได้)
     * เซิร์ฟเวอร์ขอทีละก้อนจาก agent แล้วสตรีมออก ไม่เก็บค้าง
     */
    public function exportDoctorRecords(Request $request, \App\Services\OpohRecordsService $svc)
    {
        [$ctx, $err] = $this->resolveDoctorRecordsContext($request);
        if ($err) {
            return $err;
        }

        $base = [
            'type'       => $ctx['type'],
            'cid'        => trim((string)$ctx['doctor']->cid),
            'licenseno'  => trim((string)$ctx['doctor']->licenseno),
            'start_date' => $ctx['start'],
            'end_date'   => $ctx['end'],
            'search'     => (string)$request->input('search', ''),
            'order_col'  => 'date',
            'order_dir'  => 'asc',
            'summary'    => false,
        ];

        // ลองดึงก้อนแรกก่อนเริ่มสตรีม เพื่อแจ้งข้อผิดพลาดเป็น JSON ได้ถ้า agent ไม่ตอบ
        $chunk = 500;
        $first = $svc->fetch($ctx['hospcode'], $base + ['offset' => 0, 'limit' => $chunk]);
        if (!$first['success']) {
            $this->auditDoctorRecords($request, $ctx, 'OPOH_DOCTOR_EXPORT', 'ERROR', 0, $first['message']);
            return response()->json(['success' => false, 'message' => $first['message']], 502);
        }

        $isIpd = $ctx['type'] === 'ipd';
        $fileName = sprintf('opoh_%s_%s_%s_%s-%s.csv', $ctx['type'], $ctx['hospcode'], $ctx['doctor']->licenseno ?: $ctx['doctor']->id, $ctx['start'], $ctx['end']);

        return response()->streamDownload(function () use ($svc, $ctx, $base, $first, $chunk, $isIpd, $request) {
            @set_time_limit(0);
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM สำหรับ Excel

            $head = $isIpd
                ? ['AN', 'HN', 'ชื่อ-สกุลผู้ป่วย', 'วันที่รับ', 'วันที่จำหน่าย', 'เวลาจำหน่าย', 'สิทธิ', 'PDX', 'โรคหลัก', 'DX0', 'DX1', 'DX2', 'DX3', 'DX4', 'DX5', 'วันนอน', 'AdjRW', 'ค่ารักษาทั้งหมด (บาท)']
                : ['VN', 'HN', 'ชื่อ-สกุลผู้ป่วย', 'วันที่', 'เวลา', 'สิทธิ', 'PDX', 'โรคหลัก', 'DX0', 'DX1', 'DX2', 'DX3', 'DX4', 'DX5', 'ค่ารักษาทั้งหมด (บาท)'];
            fputcsv($out, $head);

            $offset = 0;
            $exported = 0;
            $data = $first['data'];
            while (true) {
                $rows = $data['rows'] ?? [];
                foreach ($rows as $r) {
                    $dx = [$r['dx0'] ?? '', $r['dx1'] ?? '', $r['dx2'] ?? '', $r['dx3'] ?? '', $r['dx4'] ?? '', $r['dx5'] ?? ''];
                    $total = number_format((float)($r['total'] ?? 0), 2, '.', '');
                    $line = $isIpd
                        ? array_merge([$r['key'] ?? '', $r['hn'] ?? '', $r['patient_name'] ?? '', $r['adm_date'] ?? '', $r['date'] ?? '', $r['time'] ?? '', $r['pttype'] ?? '', $r['pdx'] ?? '', $r['diag_name'] ?? ''], $dx, [$r['los'] ?? '', $r['adjrw'] ?? '', $total])
                        : array_merge([$r['key'] ?? '', $r['hn'] ?? '', $r['patient_name'] ?? '', $r['date'] ?? '', $r['time'] ?? '', $r['pttype'] ?? '', $r['pdx'] ?? '', $r['diag_name'] ?? ''], $dx, [$total]);
                    fputcsv($out, $line);
                    $exported++;
                }
                $offset += $chunk;
                $filtered = (int)($data['records_filtered'] ?? 0);
                if (empty($rows) || $offset >= $filtered) {
                    break;
                }
                $next = $svc->fetch($ctx['hospcode'], $base + ['offset' => $offset, 'limit' => $chunk]);
                if (!$next['success']) {
                    fputcsv($out, ['!! ส่งออกไม่ครบ: ' . $next['message']]);
                    break;
                }
                $data = $next['data'];
            }
            fclose($out);
            $this->auditDoctorRecords($request, $ctx, 'OPOH_DOCTOR_EXPORT', 'SUCCESS', $exported);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * ส่งออกรายเคส (Indiv) ของแพทย์ทุกคนใน รพ. 1 แห่ง เป็น CSV: เลือก OPD / IPD / ทั้งหมด
     * มีคอลัมน์ชื่อแพทย์ ชื่อ รพ. และยอดแยกตามหมวด drg_chrgitem ทุกหมวด (ดึงสดผ่าน agent)
     */
    public function exportIndiv(Request $request, \App\Services\OpohRecordsService $svc)
    {
        if (!Auth::check() || !Auth::user()->canAccessOpoh()) {
            return response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์ดูข้อมูลรายเคส (ต้องมีสิทธิ์ allow_opoh)'], 403);
        }

        $hospitalsMeta  = $this->resolveOpohHospitals();
        $communityCodes = $hospitalsMeta['community_codes'];
        $type     = (string)$request->input('type', 'all');
        $hospcode = (string)$request->input('hospcode');
        $start    = (string)$request->input('start_date');
        $end      = (string)$request->input('end_date');

        if (!in_array($type, ['opd', 'ipd', 'all'], true)) {
            return response()->json(['success' => false, 'message' => 'ประเภทบริการไม่ถูกต้อง'], 422);
        }
        if (!in_array($hospcode, $communityCodes, true)) {
            return response()->json(['success' => false, 'message' => 'รองรับเฉพาะ รพช. ในเครือข่าย OPOH'], 422);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            return response()->json(['success' => false, 'message' => 'ช่วงวันที่ไม่ถูกต้อง'], 422);
        }

        // กรองไม่ส่งออกแพทย์ที่มี รพ.ต้นสังกัดตรงกับโรงพยาบาลปลายทางที่กำลัง export (ไม่นับเป็น OPOH)
        $doctors = DB::table('opoh_doctors')
            ->where('is_active', 'Y')
            ->get(['cid', 'licenseno', 'origin_hospcode']);
        $docList = [];
        foreach ($doctors as $d) {
            if (!empty($d->origin_hospcode) && (string)$d->origin_hospcode === $hospcode) {
                continue;
            }
            $cid = trim((string)$d->cid);
            $lic = trim((string)$d->licenseno);
            if ($cid !== '' || $lic !== '') {
                $docList[] = ['cid' => $cid, 'licenseno' => $lic];
            }
        }
        if (empty($docList)) {
            return response()->json(['success' => false, 'message' => 'ไม่มีแพทย์ที่ให้บริการ OPOH ณ โรงพยาบาลนี้ (หรือทั้งหมดมีต้นสังกัดที่ รพ. นี้)'], 422);
        }

        $hosp = Hospital::where('hospcode', $hospcode)->first();
        $hospName = $hosp->name ?? $hospcode;
        $types = $type === 'all' ? ['opd', 'ipd'] : [$type];
        $chunk = 500;

        $base = fn(string $t) => [
            'type' => $t, 'start_date' => $start, 'end_date' => $end,
            'hospital_name' => $hospName, 'doctors' => $docList,
        ];

        // ดึงก้อนแรกของประเภทแรกก่อนสตรีม เพื่อแจ้งข้อผิดพลาดเป็น JSON ได้ถ้า agent ไม่ตอบ
        $first = $svc->fetch($hospcode, $base($types[0]) + ['offset' => 0, 'limit' => $chunk], 25000, 'opoh_indiv_export');
        $ctx = ['type' => $type, 'hospcode' => $hospcode, 'start' => $start, 'end' => $end,
                'doctor' => (object)['doctor_name' => 'ทุกท่าน', 'licenseno' => null]];
        if (!$first['success']) {
            $this->auditDoctorRecords($request, $ctx, 'OPOH_INDIV_EXPORT', 'ERROR', 0, $first['message']);
            return response()->json(['success' => false, 'message' => $first['message']], 502);
        }

        $fileName = sprintf('opoh_indiv_%s_%s_%s-%s.csv', $type, $hospcode, $start, $end);

        return response()->streamDownload(function () use ($svc, $hospcode, $types, $base, $first, $chunk, $request, $ctx) {
            @set_time_limit(0);
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $cats = $first['data']['categories'] ?? [];
            $head = ['ประเภท', 'โรงพยาบาล', 'แพทย์', 'VN/AN', 'HN', 'ชื่อ-สกุลผู้ป่วย', 'วันที่รับ', 'วันที่ (OPD=รับบริการ, IPD=จำหน่าย)', 'เวลา',
                'สิทธิ', 'PDX', 'โรคหลัก', 'DX0', 'DX1', 'DX2', 'DX3', 'DX4', 'DX5', 'วันนอน', 'AdjRW'];
            foreach ($cats as $c) {
                $head[] = 'หมวด ' . $c['id'] . ' ' . ($c['name'] ?? '');
            }
            $head[] = 'ค่ารักษาทั้งหมด (บาท)';
            fputcsv($out, $head);

            $exported = 0;
            foreach ($types as $idx => $t) {
                $offset = 0;
                $data = $idx === 0 ? $first['data'] : null;
                while (true) {
                    if ($data === null) {
                        $next = $svc->fetch($hospcode, $base($t) + ['offset' => $offset, 'limit' => $chunk], 25000, 'opoh_indiv_export');
                        if (!$next['success']) {
                            fputcsv($out, ['!! ส่งออกไม่ครบ (' . strtoupper($t) . '): ' . $next['message']]);
                            break;
                        }
                        $data = $next['data'];
                    }
                    $rows = $data['rows'] ?? [];
                    foreach ($rows as $r) {
                        $line = [
                            strtoupper($t), $r['hospital_name'] ?? '', $r['doctor_name'] ?? '', $r['key'] ?? '', $r['hn'] ?? '',
                            $r['patient_name'] ?? '', $r['adm_date'] ?? '', $r['date'] ?? '', $r['time'] ?? '',
                            $r['pttype'] ?? '', $r['pdx'] ?? '', $r['diag_name'] ?? '',
                            $r['dx0'] ?? '', $r['dx1'] ?? '', $r['dx2'] ?? '', $r['dx3'] ?? '', $r['dx4'] ?? '', $r['dx5'] ?? '',
                            $r['los'] ?? '', $r['adjrw'] ?? '',
                        ];
                        $rc = $r['cats'] ?? [];
                        foreach ($cats as $c) {
                            $line[] = number_format((float)($rc[(string)$c['id']] ?? 0), 2, '.', '');
                        }
                        $line[] = number_format((float)($r['total'] ?? 0), 2, '.', '');
                        fputcsv($out, $line);
                        $exported++;
                    }
                    $offset += $chunk;
                    $total = (int)($data['records_total'] ?? 0);
                    $data = null;
                    if (empty($rows) || $offset >= $total) {
                        break;
                    }
                }
            }
            fclose($out);
            $this->auditDoctorRecords($request, $ctx, 'OPOH_INDIV_EXPORT', 'SUCCESS', $exported);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * AJAX endpoint: Get 16 Income Categories breakdown for a specific doctor, or ALL doctors / entire hospital
     */
    public function getDoctorIncomeCategories(Request $request, $doctorId)
    {
        $hospitalsMeta  = $this->resolveOpohHospitals();
        $communityCodes = $hospitalsMeta['community_codes'];
        $hospCount      = count($communityCodes);
        $hubHospitalName = $hospitalsMeta['hub_name'];

        $service_type = $request->input('service_type', 'ALL'); // 'ALL', 'OPD', 'IPD'
        if (!in_array($service_type, ['ALL', 'OPD', 'IPD'], true)) {
            $service_type = 'ALL';
        }
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $qStart     = ($start_date && $end_date) ? $start_date : '2000-01-01';
        $qEnd       = ($start_date && $end_date) ? $end_date : Carbon::now()->toDateString();

        if ($doctorId === 'ALL' || $doctorId === 'all' || $doctorId == '0') {
            $doctor = (object)[
                'id'             => 'ALL',
                'doctor_name'    => "ยอดรวมทั้งหมด รพช. {$hospCount} แห่ง (ไม่แยกแพทย์)",
                'licenseno'      => 'รวมทุกแพทย์ / ทุกหน่วยบริการ (ไม่นับซ้ำราย AN)',
                'spclty_code'    => 'ALL',
                'specialty_name' => 'ภาพรวมทุกสาขาความเชี่ยวชาญ',
            ];
            $query = $this->incomeCategoriesQuery($service_type, $communityCodes, $qStart, $qEnd);
        } elseif ($doctorId === 'AOPH' || $doctorId === 'aoph') {
            $activeDocCount = OpohDoctor::where('is_active', 'Y')->count();
            $doctor = (object)[
                'id'             => 'AOPH',
                'doctor_name'    => "ยอดรวมเฉพาะแพทย์ OPOH ทั้งหมด ({$activeDocCount} ท่าน)",
                'licenseno'      => "แพทย์เฉพาะทาง (ไม่รวม {$hubHospitalName} และ รพ.ต้นสังกัด)",
                'spclty_code'    => 'AOPH',
                'specialty_name' => 'รวม 7 สาขาเฉพาะทาง OPOH',
            ];
            $query = DB::table('opoh_income_categories as ic')
                ->whereIn('ic.hospcode', $communityCodes)
                ->whereNotNull('ic.doctor_id');
            $this->applyOriginExclusion($query, 'ic');
            if ($service_type !== 'ALL') {
                $query->where('ic.service_type', $service_type);
            }
            if ($start_date && $end_date) {
                $query->whereBetween('ic.vstdate', [$start_date, $end_date]);
            }
        } else {
            $doctor = DB::table('opoh_doctors')->where('id', $doctorId)->first();
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลแพทย์'], 404);
            }
            $query = DB::table('opoh_income_categories')
                ->where('doctor_id', $doctorId)
                ->whereIn('hospcode', $communityCodes);
            if (!empty($doctor->origin_hospcode)) {
                $query->where('hospcode', '!=', $doctor->origin_hospcode);
            }
            if ($service_type !== 'ALL') {
                $query->where('service_type', $service_type);
            }
            if ($start_date && $end_date) {
                $query->whereBetween('vstdate', [$start_date, $end_date]);
            }
        }

        $rawRows = $query->selectRaw("
            drg_chrgitem_id,
            drg_chrgitem_name,
            COALESCE(SUM(inc_ucs_incup), 0)   AS ucs_incup,
            COALESCE(SUM(inc_ucs_outprov), 0) AS ucs_outprov,
            COALESCE(SUM(inc_ofc), 0)         AS ofc,
            COALESCE(SUM(inc_lgo), 0)         AS lgo,
            COALESCE(SUM(inc_sss), 0)         AS sss,
            COALESCE(SUM(inc_other), 0)       AS other,
            COALESCE(SUM(inc_total), 0)       AS total
        ")
        ->groupBy('drg_chrgitem_id', 'drg_chrgitem_name')
        ->get()
        ->keyBy('drg_chrgitem_id');

        $masterCategories = [
            1  => '1. ค่าห้องและค่าอาหาร',
            2  => '2. อวัยวะเทียมและอุปกรณ์ในการบำบัดรักษา',
            3  => '3. ยาและสารอาหารทางเส้นเลือดที่ใช้ใน รพ.',
            4  => '4. ยาที่นำไปใช้ต่อที่บ้าน',
            5  => '5. เวชภัณฑ์ที่มิใช่ยา',
            6  => '6. บริการโลหิตและส่วนประกอบของโลหิต',
            7  => '7. ตรวจวินิจฉัยทางเทคนิคการแพทย์และพยาธิวิทยา (Lab)',
            8  => '8. ตรวจวินิจฉัยและรักษาทางรังสีวิทยา (X-Ray)',
            9  => '9. ตรวจวินิจฉัยโดยวิธีพิเศษอื่นๆ',
            10 => '10. อุปกรณ์ของใช้และเครื่องมือทางการแพทย์',
            11 => '11. ทำหัตถการและวิสัญญี',
            12 => '12. ค่าบริการทางการพยาบาล',
            13 => '13. ค่าบริการทางทันตกรรม',
            14 => '14. ค่าบริการทางกายภาพบำบัดและเวชกรรมฟื้นฟู',
            15 => '15. ค่าบริการฝังเข็มและการบำบัดของผู้ประกอบโรคศิลปะอื่น',
            16 => '16. ค่าบริการอื่นๆ ที่ไม่เกี่ยวกับการรักษาพยาบาลโดยตรง',
        ];

        $categories = [];
        $totals = [
            'ucs_incup'   => 0.0,
            'ucs_outprov' => 0.0,
            'ofc'         => 0.0,
            'lgo'         => 0.0,
            'sss'         => 0.0,
            'other'       => 0.0,
            'total'       => 0.0,
        ];

        foreach ($masterCategories as $catId => $catName) {
            $row = $rawRows->get($catId);
            $uIn  = (float)($row->ucs_incup ?? 0.0);
            $uOut = (float)($row->ucs_outprov ?? 0.0);
            $ofc  = (float)($row->ofc ?? 0.0);
            $lgo  = (float)($row->lgo ?? 0.0);
            $sss  = (float)($row->sss ?? 0.0);
            $oth  = (float)($row->other ?? 0.0);
            $tot  = (float)($row->total ?? 0.0);

            $categories[] = [
                'drg_chrgitem_id'   => $catId,
                'drg_chrgitem_name' => $catName,
                'ucs_incup'         => $uIn,
                'ucs_outprov'       => $uOut,
                'ofc'               => $ofc,
                'lgo'               => $lgo,
                'sss'               => $sss,
                'other'             => $oth,
                'total'             => $tot,
            ];

            $totals['ucs_incup']   += $uIn;
            $totals['ucs_outprov'] += $uOut;
            $totals['ofc']         += $ofc;
            $totals['lgo']         += $lgo;
            $totals['sss']         += $sss;
            $totals['other']       += $oth;
            $totals['total']       += $tot;
        }

        return response()->json([
            'success'      => true,
            'doctor'       => [
                'id'             => $doctor->id,
                'doctor_name'    => $doctor->doctor_name,
                'licenseno'      => $doctor->licenseno,
                'spclty_code'    => $doctor->spclty_code,
                'specialty_name' => $doctor->specialty_name,
            ],
            'service_type' => $service_type,
            'date_range'   => ['start' => $start_date, 'end' => $end_date],
            'categories'   => $categories,
            'totals'       => $totals,
        ]);
    }
}

