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

        // 3. Target Community Hospitals definition (รพช. 6 แห่ง สำหรับตัวชี้วัด OPOH)
        $communityHospitals = [
            '10990' => ['name' => 'รพ.ลืออำนาจ', 'order' => 1],
            '10986' => ['name' => 'รพ.ปทุมราชวงศา', 'order' => 2],
            '10989' => ['name' => 'รพ.หัวตะพาน', 'order' => 3],
            '10988' => ['name' => 'รพ.เสนางคนิคม', 'order' => 4],
            '10987' => ['name' => 'รพ.พนา', 'order' => 5],
            '10985' => ['name' => 'รพ.ชานุมาน', 'order' => 6],
        ];
        $communityCodes = array_keys($communityHospitals);

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
            ->selectRaw("
                spclty_code,
                COALESCE(SUM(visit_total), 0) AS total_visits,
                COALESCE(SUM(hn_total), 0) AS total_hns
            ")
            ->groupBy('spclty_code')
            ->pluck('total_visits', 'spclty_code')
            ->toArray();

        // 5.2 Hospital Breakdown: Past, Current Period, Cumulative Total
        // Past records: จาก 2025-10-01 ถึงก่อน $current_start
        $pastStatsByHosp = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<', $current_start)
            ->selectRaw("
                hospcode,
                COALESCE(SUM(visit_total), 0) AS past_visits,
                COALESCE(SUM(hn_total), 0) AS past_hns
            ")
            ->groupBy('hospcode')
            ->pluck('past_visits', 'hospcode')
            ->toArray();

        // Current period records
        $currentStatsByHosp = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->selectRaw("
                hospcode,
                COALESCE(SUM(visit_total), 0) AS current_visits,
                COALESCE(SUM(hn_total), 0) AS current_hns
            ")
            ->groupBy('hospcode')
            ->pluck('current_visits', 'hospcode')
            ->toArray();

        // Current period by hospital and specialty
        $hospSpecialtyMatrix = [];
        $matrixRaw = DB::table('opoh_opd_service')
            ->whereBetween('vstdate', [$current_start, $current_end])
            ->selectRaw("hospcode, spclty_code, COALESCE(SUM(visit_total), 0) AS visits")
            ->groupBy('hospcode', 'spclty_code')
            ->get();

        foreach ($matrixRaw as $row) {
            $hospSpecialtyMatrix[$row->hospcode][$row->spclty_code] = $row->visits;
        }

        // Cumulative all-time (จาก 2025-10-01 ถึง $current_end) by hospital
        $totalStatsByHosp = DB::table('opoh_opd_service')
            ->where('vstdate', '>=', $cumulative_start)
            ->where('vstdate', '<=', $current_end)
            ->selectRaw("
                hospcode,
                COALESCE(SUM(visit_total), 0) AS total_visits,
                COALESCE(SUM(hn_total), 0) AS total_hns
            ")
            ->groupBy('hospcode')
            ->pluck('total_visits', 'hospcode')
            ->toArray();

        // Build Hospital Rows (เฉพาะ รพช. 6 แห่ง เป็นยอดบริการ OPOH)
        $hospitalRows = [];
        $sumPast = 0;
        $sumCurrent = 0;
        $sumCumulative = 0;

        foreach ($communityHospitals as $code => $info) {
            $past = (int)($pastStatsByHosp[$code] ?? 0);
            $curr = (int)($currentStatsByHosp[$code] ?? 0);
            $total = (int)($totalStatsByHosp[$code] ?? ($past + $curr));

            $sumPast += $past;
            $sumCurrent += $curr;
            $sumCumulative += $total;

            $hospitalRows[] = [
                'hospcode'       => $code,
                'name'           => $info['name'],
                'past_visits'    => $past,
                'current_visits' => $curr,
                'total_visits'   => $total,
                'specialties'    => $hospSpecialtyMatrix[$code] ?? [],
            ];
        }

        // ข้อมูลอ้างอิง รพ.อำนาจเจริญ (ตรวจ ณ รพ.แม่ข่าย 10703 ไม่รวมในยอด OPOH)
        $amnatPast = (int)($pastStatsByHosp['10703'] ?? 0);
        $amnatCurr = (int)($currentStatsByHosp['10703'] ?? 0);
        $amnatTotal = (int)($totalStatsByHosp['10703'] ?? ($amnatPast + $amnatCurr));
        $amnatRow = [
            'hospcode'       => '10703',
            'name'           => 'รพ.อำนาจเจริญ (แม่ข่าย)',
            'past_visits'    => $amnatPast,
            'current_visits' => $amnatCurr,
            'total_visits'   => $amnatTotal,
            'specialties'    => $hospSpecialtyMatrix['10703'] ?? [],
        ];

        // 5.3 Financial & Scheme Totals (Current Period & Cumulative เฉพาะ รพช. 6 แห่ง)
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

        // 5.4 Cumulative Financial Summary (ตั้งแต่ 2025-10-01 ถึง $current_end เฉพาะ รพช. 6 แห่ง)
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

        // 5.5 IPD Aggregations (ผู้ป่วยใน)
        // Cumulative IPD cards by specialty (รพช. 6 แห่ง ยอดสะสมถึง $current_end)
        $ipdSpecialtyStats = DB::table('opoh_ipd_service')
            ->where('dchdate', '<=', $current_end)
            ->whereIn('hospcode', $communityCodes)
            ->selectRaw("spclty_code, COALESCE(SUM(an_total), 0) AS total_ans")
            ->groupBy('spclty_code')
            ->pluck('total_ans', 'spclty_code')
            ->toArray();

        // Past IPD records by hospital (สะสมในอดีตก่อนช่วง $current_start)
        $ipdPastStatsByHosp = DB::table('opoh_ipd_service')
            ->where('dchdate', '<', $current_start)
            ->selectRaw("hospcode, COALESCE(SUM(an_total), 0) AS past_ans")
            ->groupBy('hospcode')
            ->pluck('past_ans', 'hospcode')
            ->toArray();

        // Current period IPD records by hospital
        $ipdCurrentStatsByHospRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->selectRaw("
                hospcode,
                COALESCE(SUM(an_total), 0)        AS current_ans,
                COALESCE(SUM(admdate_total), 0)   AS current_los,
                COALESCE(SUM(adjrw_total), 0)     AS current_rw,
                COALESCE(SUM(inc_total), 0)       AS current_inc
            ")
            ->groupBy('hospcode')
            ->get();
        $ipdCurrentStatsByHosp = [];
        foreach ($ipdCurrentStatsByHospRaw as $row) {
            $ipdCurrentStatsByHosp[$row->hospcode] = $row;
        }

        // Current period IPD by hospital and specialty
        $ipdHospSpecialtyMatrix = [];
        $ipdMatrixRaw = DB::table('opoh_ipd_service')
            ->whereBetween('dchdate', [$current_start, $current_end])
            ->selectRaw("hospcode, spclty_code, COALESCE(SUM(an_total), 0) AS ans")
            ->groupBy('hospcode', 'spclty_code')
            ->get();
        foreach ($ipdMatrixRaw as $row) {
            $ipdHospSpecialtyMatrix[$row->hospcode][$row->spclty_code] = (int)$row->ans;
        }

        // Cumulative all-time IPD by hospital (ยอดสะสมถึง $current_end)
        $ipdTotalStatsByHospRaw = DB::table('opoh_ipd_service')
            ->where('dchdate', '<=', $current_end)
            ->selectRaw("
                hospcode,
                COALESCE(SUM(an_total), 0)        AS total_ans,
                COALESCE(SUM(admdate_total), 0)   AS total_los,
                COALESCE(SUM(adjrw_total), 0)     AS total_rw,
                COALESCE(SUM(inc_total), 0)       AS total_inc
            ")
            ->groupBy('hospcode')
            ->get();
        $ipdTotalStatsByHosp = [];
        foreach ($ipdTotalStatsByHospRaw as $row) {
            $ipdTotalStatsByHosp[$row->hospcode] = $row;
        }

        // Build IPD Hospital Rows (เฉพาะ รพช. 6 แห่ง เป็นยอดบริการ OPOH)
        $ipdHospitalRows = [];
        $ipdSumPast = 0;
        $ipdSumCurrent = 0;
        $ipdSumCumulative = 0;
        $ipdSumLos = 0;
        $ipdSumRw = 0;
        $ipdSumInc = 0;

        foreach ($communityHospitals as $code => $info) {
            $past = (int)($ipdPastStatsByHosp[$code] ?? 0);
            $curr = (int)($ipdCurrentStatsByHosp[$code]->current_ans ?? 0);
            $total = (int)($ipdTotalStatsByHosp[$code]->total_ans ?? ($past + $curr));
            $los = (int)($ipdTotalStatsByHosp[$code]->total_los ?? 0);
            $rw = (float)($ipdTotalStatsByHosp[$code]->total_rw ?? 0.0);
            $inc = (float)($ipdTotalStatsByHosp[$code]->total_inc ?? 0.0);

            $ipdSumPast += $past;
            $ipdSumCurrent += $curr;
            $ipdSumCumulative += $total;
            $ipdSumLos += $los;
            $ipdSumRw += $rw;
            $ipdSumInc += $inc;

            $ipdHospitalRows[] = [
                'hospcode'     => $code,
                'name'         => $info['name'],
                'past_ans'     => $past,
                'current_ans'  => $curr,
                'total_ans'    => $total,
                'total_los'    => $los,
                'total_rw'     => $rw,
                'total_inc'    => $inc,
                'specialties'  => $ipdHospSpecialtyMatrix[$code] ?? [],
            ];
        }

        // ข้อมูลอ้างอิง รพ.อำนาจเจริญ (IPD ณ รพ.แม่ข่าย 10703 ไม่รวมในยอด OPOH)
        $ipdAmnatPast = (int)($ipdPastStatsByHosp['10703'] ?? 0);
        $ipdAmnatCurr = (int)($ipdCurrentStatsByHosp['10703']->current_ans ?? 0);
        $ipdAmnatTotal = (int)($ipdTotalStatsByHosp['10703']->total_ans ?? ($ipdAmnatPast + $ipdAmnatCurr));
        $ipdAmnatLos = (int)($ipdTotalStatsByHosp['10703']->total_los ?? 0);
        $ipdAmnatRw = (float)($ipdTotalStatsByHosp['10703']->total_rw ?? 0.0);
        $ipdAmnatInc = (float)($ipdTotalStatsByHosp['10703']->total_inc ?? 0.0);
        $ipdAmnatRow = [
            'hospcode'     => '10703',
            'name'         => 'รพ.อำนาจเจริญ (แม่ข่าย)',
            'past_ans'     => $ipdAmnatPast,
            'current_ans'  => $ipdAmnatCurr,
            'total_ans'    => $ipdAmnatTotal,
            'total_los'    => $ipdAmnatLos,
            'total_rw'     => $ipdAmnatRw,
            'total_inc'    => $ipdAmnatInc,
            'specialties'  => $ipdHospSpecialtyMatrix['10703'] ?? [],
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
            'hospitalRows',
            'amnatRow',
            'sumPast',
            'sumCurrent',
            'sumCumulative',
            'summary',
            'fiscalSummary',
            'ipdSpecialtyStats',
            'ipdHospitalRows',
            'ipdAmnatRow',
            'ipdSumPast',
            'ipdSumCurrent',
            'ipdSumCumulative',
            'ipdSumLos',
            'ipdSumRw',
            'ipdSumInc'
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

