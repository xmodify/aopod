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

        // Build Specialty Rows for OPD Table (7 สาขาเป็นแถว และ รพ.เป็นคอลัมน์)
        $specialtyRows = [];
        $specColTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $specColTotals[$hcode] = 0;
        }

        foreach ($specialties as $code => $spec) {
            $cumul = (int)($specialtyStats[$code] ?? 0);
            $period = (int)($specialtyPeriodVisits[$code] ?? 0);
            $amnatVisits = (int)($specHospMatrix[$code]['10703'] ?? 0);

            $specColTotals['cumulative'] += $cumul;
            $specColTotals['period'] += $period;
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
                COALESCE(SUM(inc_total), 0)       AS inc_total,
                COALESCE(SUM(inc_drug_total), 0)  AS inc_drug_total,
                COALESCE(SUM(inc_lab_total), 0)   AS inc_lab_total,
                COALESCE(SUM(refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

        $fiscalSummary = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("
                COALESCE(SUM(visit_total), 0)     AS visit_total,
                COALESCE(SUM(inc_total), 0)       AS inc_total,
                COALESCE(SUM(inc_drug_total), 0)  AS inc_drug_total,
                COALESCE(SUM(inc_lab_total), 0)   AS inc_lab_total,
                COALESCE(SUM(refer_to_origin), 0) AS refer_to_origin,
                COALESCE(SUM(admit_ipd), 0)       AS admit_ipd
            ")
            ->first();

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

        $ipdPeriodInc = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
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
            'pointRadius' => 4,
            'pointHoverRadius' => 6,
            'tension' => 0.3,
            'fill' => false,
            'data' => array_values($monthlyIpdTotals),
        ];

        $ipdMonthlyChart = [
            'labels' => $monthLabels,
            'datasets' => $ipdChartDatasets,
        ];

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

        return view('dashboard_opoh', compact(
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
            'ipdMonthlyChart'
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

        // Build Doctor Service Stats Rows
        $docStats = [];
        $colTotals = [
            'cumulative' => 0,
            'period'     => 0,
            'amnat'      => 0,
        ];
        foreach ($hospitalsList as $hcode => $hname) {
            $colTotals[$hcode] = 0;
        }

        foreach ($doctors as $doc) {
            $cumul = (int)($docCumulativeVisits[$doc->id] ?? 0);
            $period = (int)($docPeriodVisits[$doc->id] ?? 0);
            $amnatVisits = (int)($docAmnatPeriodVisits[$doc->id] ?? 0);

            $colTotals['cumulative'] += $cumul;
            $colTotals['period'] += $period;
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
                'hosp_visits'  => $hospVisits,
                'amnat_visits' => $amnatVisits,
            ];
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

        $docPeriodInc = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
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
            'docIpdStats',
            'colIpdTotals',
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
}

