<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\OpohDoctor;
use App\Models\OpohOpdService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardOpohController extends Controller
{
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

        // 3. Target Community Hospitals definition (รพช. 6 แห่ง เรียงลำดับตรงกันทุกหน้า)
        $hospitalsList = [
            '10985' => 'รพ.ชานุมาน',
            '10986' => 'รพ.ปทุมราชวงศา',
            '10987' => 'รพ.พนา',
            '10988' => 'รพ.เสนางคนิคม',
            '10989' => 'รพ.หัวตะพาน',
            '10990' => 'รพ.ลืออำนาจ',
        ];
        $communityCodes = array_keys($hospitalsList);

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
        // ยอดสะสม fix เริ่มต้นจาก ต.ค. 68 (2025-10-01) ถึง $current_end
        $cumulative_start = '2025-10-01';

        // 5.1 Cumulative total cards by specialty (รพช. 6 แห่ง ตั้งแต่ ต.ค. 68)
        $specialtyStats = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'spclty_code')
            ->toArray();

        // 5.2 Current period visits per specialty ($current_start ถึง $current_end) เฉพาะ รพช. 6 แห่ง
        $specialtyPeriodVisits = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'spclty_code')
            ->toArray();

        // 5.3 Current period by specialty and hospcode (รพช. 6 แห่ง + รพ.อำนาจเจริญ แม่ข่าย)
        $specHospMatrixRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->groupBy('spclty_code', 'hospcode')
            ->selectRaw('spclty_code, hospcode, COALESCE(SUM(visit_total), 0) as visits')
            ->get();

        $specHospMatrix = [];
        foreach ($specHospMatrixRaw as $m) {
            $specHospMatrix[$m->spclty_code][$m->hospcode] = (int)$m->visits;
        }

        $specialtyPeriodInc = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(inc_total), 0)'), 'spclty_code')
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
            $amnatVisits = (int)($specHospMatrix[$code]['10703'] ?? 0);

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

        // 5.4 Financial & Scheme Totals (Current Period & Cumulative เฉพาะ รพช. 6 แห่ง)
        $summary = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(visit_total), 0)     AS visit_total,
                COALESCE(SUM(hn_total), 0)        AS hn_total,
                COALESCE(SUM(visit_ucs), 0)       AS visit_ucs,
                COALESCE(SUM(visit_ofc), 0)       AS visit_ofc,
                COALESCE(SUM(visit_lgo), 0)       AS visit_lgo,
                COALESCE(SUM(visit_sss), 0)       AS visit_sss,
                COALESCE(SUM(visit_other), 0)     AS visit_other,
                COALESCE(SUM(visit_telemed), 0)   AS visit_telemed,
                COALESCE(SUM(refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

        // Income breakdown for OPD Current Period from opoh_income_categories
        $opdPeriodIncSum = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
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

        $fiscalSummary = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(visit_total), 0)     AS visit_total,
                COALESCE(SUM(visit_telemed), 0)   AS visit_telemed,
                COALESCE(SUM(refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

        $opdCumulativeIncSum = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
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
        $ipdSpecialtyStats = DB::table('opoh_ipd_service')
            ->where('dchdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'spclty_code')
            ->toArray();

        $ipdPeriodStats = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'spclty_code')
            ->toArray();

        $ipdPeriodLos = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(admdate_total), 0)'), 'spclty_code')
            ->toArray();

        $ipdPeriodRw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(adjrw_total), 0)'), 'spclty_code')
            ->toArray();

        $ipdPeriodInc = DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('spclty_code')
            ->pluck(DB::raw('COALESCE(SUM(inc_total), 0)'), 'spclty_code')
            ->toArray();

        $ipdSpecHospMatrixRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->groupBy('spclty_code', 'hospcode')
            ->selectRaw('spclty_code, hospcode, COALESCE(SUM(an_total), 0) as ans')
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
            $amnatAns = (int)($ipdSpecHospMatrix[$code]['10703'] ?? 0);

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

        $ipdSumPast = max(0, $ipdSpecColTotals['cumulative'] - $ipdSpecColTotals['period']);
        $ipdSumCurrent = $ipdSpecColTotals['period'];
        $ipdSumCumulative = $ipdSpecColTotals['cumulative'];
        $ipdSumLos = $ipdSpecColTotals['los'];
        $ipdSumRw = $ipdSpecColTotals['rw'];
        $ipdSumInc = $ipdSpecColTotals['inc'];

        // 5.6 กราฟแท่งแนวโน้ม 12 เดือนของปีงบประมาณ (Monthly Trend Chart)
        $hospColors = [
            '10985' => '#0284c7', // ชานุมาน (Sky Blue)
            '10986' => '#10b981', // ปทุมราชวงศา (Emerald)
            '10987' => '#8b5cf6', // พนา (Purple)
            '10988' => '#f59e0b', // เสนางคนิคม (Amber)
            '10989' => '#ec4899', // หัวตะพาน (Pink)
            '10990' => '#06b6d4', // ลืออำนาจ (Cyan)
        ];

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
        $monthlyOpdRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$fiscal_start, $fiscal_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("hospcode, DATE_FORMAT(vstdate, '%Y-%m') as ym, COALESCE(SUM(visit_total), 0) as visits")
            ->groupBy('hospcode', 'ym')
            ->get();

        $monthlyOpdMatrix = [];
        foreach ($monthlyOpdRaw as $row) {
            $monthlyOpdMatrix[$row->hospcode][$row->ym] = (int)$row->visits;
        }

        $opdChartDatasets = [];
        $monthlyOpdTotals = array_fill_keys($monthKeys, 0);

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
                'backgroundColor' => $hospColors[$hcode] ?? '#64748b',
                'borderRadius' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
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

        // IPD Monthly Data for Chart
        $monthlyIpdRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$fiscal_start, $fiscal_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("hospcode, DATE_FORMAT(dchdate, '%Y-%m') as ym, COALESCE(SUM(an_total), 0) as ans")
            ->groupBy('hospcode', 'ym')
            ->get();

        $monthlyIpdMatrix = [];
        foreach ($monthlyIpdRaw as $row) {
            $monthlyIpdMatrix[$row->hospcode][$row->ym] = (int)$row->ans;
        }

        $ipdChartDatasets = [];
        $monthlyIpdTotals = array_fill_keys($monthKeys, 0);

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
                'backgroundColor' => $hospColors[$hcode] ?? '#64748b',
                'borderRadius' => 4,
                'minBarLength' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
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

        $opSummary = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(case_total), 0) as total,
                COALESCE(SUM(CASE WHEN op_type = 'IPD' THEN case_total ELSE 0 END), 0) as total_ipd,
                COALESCE(SUM(CASE WHEN op_type = 'ODS' THEN case_total ELSE 0 END), 0) as total_ods,
                0 as total_inc
            ")
            ->first();

        $opSumCumulative = (int)DB::table('opoh_operation_service')
            ->where('operation_date', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->sum('case_total');

        // Top 5 Diagnoses & Operations for Right-Hand (5-Col) Cards
        $topOpdDiagnoses = DB::table('opoh_opd_diagnoses')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw('icd10, diag_name, COALESCE(SUM(case_total), 0) as total')
            ->groupBy('icd10', 'diag_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topIpdDiagnoses = DB::table('opoh_ipd_diagnoses')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw('icd10, diag_name, COALESCE(SUM(case_total), 0) as total')
            ->groupBy('icd10', 'diag_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topOperations = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw('icd9, operation_name, COALESCE(SUM(case_total), 0) as total')
            ->groupBy('icd9', 'operation_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Operation Monthly Data for Chart (Tab 3)
        $monthlyOpRaw = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$fiscal_start, $fiscal_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("hospcode, DATE_FORMAT(operation_date, '%Y-%m') as ym, COALESCE(SUM(case_total), 0) as cases")
            ->groupBy('hospcode', 'ym')
            ->get();

        $monthlyOpMatrix = [];
        foreach ($monthlyOpRaw as $row) {
            $monthlyOpMatrix[$row->hospcode][$row->ym] = (int)$row->cases;
        }

        $opChartDatasets = [];
        $monthlyOpTotals = array_fill_keys($monthKeys, 0);

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
                'backgroundColor' => $hospColors[$hcode] ?? '#64748b',
                'borderRadius' => 4,
                'minBarLength' => 4,
                'data' => $data,
                'stack' => 'stack0',
            ];
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

        $opSpecHospMatrixRaw = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$current_start, $current_end])
            ->groupBy('spclty_code', 'hospcode')
            ->selectRaw('spclty_code, hospcode, COALESCE(SUM(case_total), 0) as cases')
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
            $amnatCases = (int)($opSpecHospMatrix[$code]['10703'] ?? 0);
            $opSpecColTotals['period'] += $period;
            $opSpecColTotals['amnat'] += $amnatCases;

            $opSpecialtyRows[] = [
                'code'        => $code,
                'name'        => $spec['name'],
                'color'       => $spec['color'],
                'icon'        => $spec['icon'],
                'period'      => $period,
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
        $opdRawRights = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(visit_ucs_incup), 0)   AS v_ucs_incup,
                COALESCE(SUM(visit_ucs_outprov), 0) AS v_ucs_outprov,
                COALESCE(SUM(visit_ucs), 0)         AS v_ucs_total,
                COALESCE(SUM(visit_ofc), 0)         AS v_ofc,
                COALESCE(SUM(visit_lgo), 0)         AS v_lgo,
                COALESCE(SUM(visit_sss), 0)         AS v_sss,
                COALESCE(SUM(visit_other), 0)       AS v_other,
                COALESCE(SUM(visit_total), 0)       AS v_total
            ")
            ->first();

        $opdRightsInc = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
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

        // 5.8.2 IPD 6 Rights Summary (แยกตาม AdjRW และ CMI สำหรับ Clearing)
        $ipdRawRights = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
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

        $ipdRightsInc = DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
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
        $opRawRights = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(case_ucs_incup), 0)   AS case_ucs_incup,
                COALESCE(SUM(case_ucs_outprov), 0) AS case_ucs_outprov,
                COALESCE(SUM(case_ofc), 0)         AS case_ofc,
                COALESCE(SUM(case_lgo), 0)         AS case_lgo,
                COALESCE(SUM(case_sss), 0)         AS case_sss,
                COALESCE(SUM(case_other), 0)       AS case_other,
                COALESCE(SUM(case_total), 0)       AS case_total,
                COALESCE(SUM(adjrw_total), 0)      AS adjrw_total
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

        // 5.8.4 Hospital Breakdown for Modal Popup on Scheme/Card Click
        $allModalHospitals = [
            '10985' => ['name' => 'รพ.ชานุมาน', 'type' => 'รพช.'],
            '10986' => ['name' => 'รพ.ปทุมราชวงศา', 'type' => 'รพช.'],
            '10987' => ['name' => 'รพ.พนา', 'type' => 'รพช.'],
            '10988' => ['name' => 'รพ.เสนางคนิคม', 'type' => 'รพช.'],
            '10989' => ['name' => 'รพ.หัวตะพาน', 'type' => 'รพช.'],
            '10990' => ['name' => 'รพ.ลืออำนาจ', 'type' => 'รพช.'],
            '10703' => ['name' => 'รพ.อำนาจเจริญ', 'type' => 'รพ.แม่ข่าย'],
        ];
        $allHospCodes = array_keys($allModalHospitals);

        // A. OPD Breakdown by Hospcode & Right
        $opdHospRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
            ->groupBy('hospcode')
            ->selectRaw("
                hospcode,
                COALESCE(SUM(visit_ucs_incup), 0)   AS v_ucs_incup,
                COALESCE(SUM(visit_ucs_outprov), 0) AS v_ucs_outprov,
                COALESCE(SUM(visit_ucs), 0)         AS v_ucs_total,
                COALESCE(SUM(visit_ofc), 0)         AS v_ofc,
                COALESCE(SUM(visit_lgo), 0)         AS v_lgo,
                COALESCE(SUM(visit_sss), 0)         AS v_sss,
                COALESCE(SUM(visit_other), 0)       AS v_other,
                COALESCE(SUM(visit_total), 0)       AS v_total
            ")
            ->get()
            ->keyBy('hospcode');

        $opdHospIncRaw = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
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

        // B. IPD Breakdown by Hospcode & Right
        $ipdHospRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
            ->groupBy('hospcode')
            ->selectRaw("
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
            ")
            ->get()
            ->keyBy('hospcode');

        $ipdHospIncRaw = DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
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
        $opHospRaw = DB::table('opoh_operation_service')
            ->whereBetween('operation_date', [$current_start, $current_end])
            ->whereIn('hospcode', $allHospCodes)
            ->groupBy('hospcode')
            ->selectRaw("
                hospcode,
                COALESCE(SUM(case_ucs_incup), 0)   AS case_ucs_incup,
                COALESCE(SUM(case_ucs_outprov), 0) AS case_ucs_outprov,
                COALESCE(SUM(case_ofc), 0)         AS case_ofc,
                COALESCE(SUM(case_lgo), 0)         AS case_lgo,
                COALESCE(SUM(case_sss), 0)         AS case_sss,
                COALESCE(SUM(case_other), 0)       AS case_other,
                COALESCE(SUM(case_total), 0)       AS case_total,
                COALESCE(SUM(adjrw_total), 0)      AS adjrw_total
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

        // Query Income Categories for the whole Fiscal Year
        $catQuery = DB::table('opoh_income_categories')
            ->whereBetween('vstdate', [$fiscal_start, $fiscal_end])
            ->whereIn('hospcode', $targetIncomeHosps);

        if ($income_service_type !== 'ALL') {
            $catQuery->where('service_type', $income_service_type);
        }

        $rawIncomeCats = $catQuery->selectRaw("
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

        // Summary Cards for Income Tab
        $incomeCumulativeGrandTotal = (float)DB::table('opoh_income_categories')
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->sum('inc_total');

        $incomePeriodGrandTotal = (float)DB::table('opoh_income_categories')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
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

        // 6 รพช. ปลายทางตามที่ระบุ (ชานุมาน, ปทุมราชวงศา, พนา, เสนางคนิคม, หัวตะพาน, ลืออำนาจ)
        $hospitalsList = [
            '10985' => 'รพ.ชานุมาน',
            '10986' => 'รพ.ปทุมราชวงศา',
            '10987' => 'รพ.พนา',
            '10988' => 'รพ.เสนางคนิคม',
            '10989' => 'รพ.หัวตะพาน',
            '10990' => 'รพ.ลืออำนาจ',
        ];
        $communityCodes = array_keys($hospitalsList);

        $cumulative_start = '2025-10-01'; // 1 ต.ค. 2568

        // 4. Query Doctor Visit Statistics
        $doctorQuery = OpohDoctor::orderBy('spclty_code')->orderBy('id');
        if (!empty($spclty_filter)) {
            $doctorQuery->where('spclty_code', $spclty_filter);
        }
        $doctors = $doctorQuery->get();

        $allDoctors = OpohDoctor::orderBy('spclty_code')->orderBy('id')->get();
        $totalDoctors = $allDoctors->count();
        $activeDoctors = $allDoctors->where('is_active', 'Y')->count();

        // 4.1 Cumulative visits per doctor (1 ต.ค. 68 - $current_end) เฉพาะ รพช. 6 แห่ง
        $docCumulativeVisits = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'doctor_id')
            ->toArray();

        // 4.2 Current period visits per doctor ($current_start - $current_end) เฉพาะ รพช. 6 แห่ง
        $docPeriodVisits = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'doctor_id')
            ->toArray();

        // 4.3 Amnat Charoen (10703) visits per doctor (ตรวจ ณ รพ.แม่ข่าย ช่วงที่เลือก)
        $docAmnatPeriodVisits = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->where('hospcode', '10703')
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'doctor_id')
            ->toArray();

        // 4.4 Current period by doctor and hospcode
        $docHospMatrixRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id', 'hospcode')
            ->selectRaw('doctor_id, hospcode, COALESCE(SUM(visit_total), 0) as visits')
            ->get();

        $docHospMatrix = [];
        foreach ($docHospMatrixRaw as $m) {
            $docHospMatrix[$m->doctor_id][$m->hospcode] = (int)$m->visits;
        }

        // 4.4.1 OPD Doctor Income from opoh_income_categories
        $docPeriodOpdInc = DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(inc_total), 0)'), 'doctor_id')
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
                $colTotals[$hcode] += $cnt;
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

        // 4.4.2 Grand Total for OPD across 6 Community Hospitals (All cases / ไม่แยกแพทย์)
        $grandOpdCumulative = (int)DB::table('opoh_opd_service')
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->sum('visit_total');

        $grandOpdPeriod = (int)DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('visit_total');

        $grandOpdHospVisitsRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('hospcode')
            ->pluck(DB::raw('COALESCE(SUM(visit_total), 0)'), 'hospcode')
            ->toArray();

        $grandOpdInc = (float)DB::table('opoh_income_categories')
            ->where('service_type', 'OPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('inc_total');

        $grandOpdAmnatVisits = (int)DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->where('hospcode', '10703')
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

        // 4.5 IPD Doctor Visit Statistics
        $docCumulativeIpd = DB::table('opoh_ipd_service')
            ->where('dchdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'doctor_id')
            ->toArray();

        $docPeriodIpd = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'doctor_id')
            ->toArray();

        $docPeriodLos = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(admdate_total), 0)'), 'doctor_id')
            ->toArray();

        $docPeriodRw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(adjrw_total), 0)'), 'doctor_id')
            ->toArray();

        $docPeriodInc = DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(inc_total), 0)'), 'doctor_id')
            ->toArray();

        // Amnat Charoen (10703) IPD visits per doctor (ตรวจ ณ รพ.แม่ข่าย ช่วงที่เลือก)
        $docAmnatPeriodIpd = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->where('hospcode', '10703')
            ->whereNotNull('doctor_id')
            ->groupBy('doctor_id')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'doctor_id')
            ->toArray();

        $docHospIpdMatrixRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
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
                $colIpdTotals[$hcode] += $cnt;
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

        // 4.5.2 Grand Total for IPD across 6 Community Hospitals (All cases / ไม่แยกแพทย์)
        $grandIpdCumulative = (int)DB::table('opoh_ipd_service')
            ->where('dchdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->sum('an_total');

        $grandIpdPeriod = (int)DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('an_total');

        $grandIpdLos = (int)DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('admdate_total');

        $grandIpdRw = (float)DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('adjrw_total');

        $grandIpdHospAnsRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->groupBy('hospcode')
            ->pluck(DB::raw('COALESCE(SUM(an_total), 0)'), 'hospcode')
            ->toArray();

        $grandIpdInc = (float)DB::table('opoh_income_categories')
            ->where('service_type', 'IPD')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->whereIn('hospcode', $communityCodes)
            ->sum('inc_total');

        $grandIpdAmnatAns = (int)DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->where('hospcode', '10703')
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
            'pname'          => ['nullable', 'string', 'max:50'],
            'fname'          => ['required', 'string', 'max:100'],
            'lname'          => ['required', 'string', 'max:100'],
            'cid'            => ['nullable', 'string', 'max:13'],
            'licenseno'      => ['nullable', 'string', 'max:20'],
            'spclty_code'    => ['required', 'string', 'max:10'],
            'specialty_name' => ['nullable', 'string', 'max:100'],
            'position'       => ['nullable', 'string', 'max:150'],
            'is_active'      => ['nullable'],
        ]);

        $specialty_name = !empty($validated['specialty_name']) ? $validated['specialty_name'] : ($specMap[$validated['spclty_code']] ?? 'อายุรกรรม');
        $doctor_name = trim(($validated['pname'] ?? '') . ' ' . $validated['fname'] . ' ' . $validated['lname']);
        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['Y', '1', 'on']) ? 'Y' : 'N';

        $doctor = OpohDoctor::create([
            'pname'          => $validated['pname'] ?? null,
            'fname'          => $validated['fname'],
            'lname'          => $validated['lname'],
            'doctor_name'    => $doctor_name,
            'cid'            => !empty($validated['cid']) ? preg_replace('/[^0-9]/', '', $validated['cid']) : null,
            'licenseno'      => !empty($validated['licenseno']) ? trim($validated['licenseno']) : null,
            'spclty_code'    => $validated['spclty_code'],
            'specialty_name' => $specialty_name,
            'position'       => $validated['position'] ?? null,
            'is_active'      => $isActive,
            'created_by'     => Auth::id(),
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
            'pname'          => ['nullable', 'string', 'max:50'],
            'fname'          => ['required', 'string', 'max:100'],
            'lname'          => ['required', 'string', 'max:100'],
            'cid'            => ['nullable', 'string', 'max:13'],
            'licenseno'      => ['nullable', 'string', 'max:20'],
            'spclty_code'    => ['required', 'string', 'max:10'],
            'specialty_name' => ['nullable', 'string', 'max:100'],
            'position'       => ['nullable', 'string', 'max:150'],
            'is_active'      => ['nullable'],
        ]);

        $specialty_name = !empty($validated['specialty_name']) ? $validated['specialty_name'] : ($specMap[$validated['spclty_code']] ?? $doctor->specialty_name);
        $doctor_name = trim(($validated['pname'] ?? '') . ' ' . $validated['fname'] . ' ' . $validated['lname']);
        $isActive = $request->has('is_active') && in_array($request->input('is_active'), ['Y', '1', 'on']) ? 'Y' : 'N';

        $doctor->update([
            'pname'          => $validated['pname'] ?? null,
            'fname'          => $validated['fname'],
            'lname'          => $validated['lname'],
            'doctor_name'    => $doctor_name,
            'cid'            => !empty($validated['cid']) ? preg_replace('/[^0-9]/', '', $validated['cid']) : null,
            'licenseno'      => !empty($validated['licenseno']) ? trim($validated['licenseno']) : null,
            'spclty_code'    => $validated['spclty_code'],
            'specialty_name' => $specialty_name,
            'position'       => $validated['position'] ?? null,
            'is_active'      => $isActive,
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

        $doctors = OpohDoctor::orderBy('spclty_code')->orderBy('id')->get()->map(function($doc) use ($specialties) {
            $specColor = $specialties[$doc->spclty_code]['color'] ?? '#0284c7';
            $specIcon = $specialties[$doc->spclty_code]['icon'] ?? 'fa-user-doctor';
            return [
                'id'             => $doc->id,
                'pname'          => $doc->pname,
                'fname'          => $doc->fname,
                'lname'          => $doc->lname,
                'doctor_name'    => $doc->doctor_name,
                'spclty_code'    => $doc->spclty_code,
                'specialty_name' => $doc->specialty_name,
                'licenseno'      => $doc->licenseno,
                'cid'            => $doc->cid,
                'position'       => $doc->position,
                'is_active'      => $doc->is_active,
                'spec_color'     => $specColor,
                'spec_icon'      => $specIcon,
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
     * AJAX endpoint: Get 16 Income Categories breakdown for a specific doctor, or ALL doctors / entire hospital
     */
    public function getDoctorIncomeCategories(Request $request, $doctorId)
    {
        $communityCodes = ['10985', '10986', '10987', '10988', '10989', '10990'];
        $service_type   = $request->input('service_type', 'ALL'); // 'ALL', 'OPD', 'IPD'
        $start_date     = $request->input('start_date');
        $end_date       = $request->input('end_date');

        if ($doctorId === 'ALL' || $doctorId === 'all' || $doctorId == '0') {
            $doctor = (object)[
                'id'             => 'ALL',
                'doctor_name'    => 'ยอดรวมทั้งหมด รพช. 6 แห่ง (ไม่แยกแพทย์)',
                'licenseno'      => 'รวมทุกแพทย์ / ทุกหน่วยบริการ',
                'spclty_code'    => 'ALL',
                'specialty_name' => 'ภาพรวมทุกสาขาความเชี่ยวชาญ',
            ];
            $query = DB::table('opoh_income_categories')
                ->whereIn('hospcode', $communityCodes);
        } elseif ($doctorId === 'AOPH' || $doctorId === 'aoph') {
            $doctor = (object)[
                'id'             => 'AOPH',
                'doctor_name'    => 'ยอดรวมเฉพาะแพทย์ AOPH ทั้งหมด (22 ท่าน)',
                'licenseno'      => 'แพทย์เฉพาะทาง รพ.แม่ข่าย',
                'spclty_code'    => 'AOPH',
                'specialty_name' => 'รวม 7 สาขาเฉพาะทาง AOPH',
            ];
            $query = DB::table('opoh_income_categories')
                ->whereIn('hospcode', $communityCodes)
                ->whereNotNull('doctor_id');
        } else {
            $doctor = DB::table('opoh_doctors')->where('id', $doctorId)->first();
            if (!$doctor) {
                return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลแพทย์'], 404);
            }
            $query = DB::table('opoh_income_categories')
                ->where('doctor_id', $doctorId);
        }

        if ($service_type !== 'ALL') {
            $query->where('service_type', $service_type);
        }

        if ($start_date && $end_date) {
            $query->whereBetween('vstdate', [$start_date, $end_date]);
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

