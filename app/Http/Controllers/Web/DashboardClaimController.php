<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// ใช้ Carbon ของ Laravel เพื่อความยืดหยุ่น (แทน date('Y-m-d'))
use Carbon\Carbon;

class DashboardClaimController extends Controller
{
    public function index(Request $request)
    {
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
        $year_data = DB::table('budget_year')
            ->whereIn('LEAVE_YEAR_ID', [$budget_year, $budget_year - 4])
            ->pluck('DATE_BEGIN', 'LEAVE_YEAR_ID');
        $start_date   = $year_data[$budget_year]     ?? null;
        $start_date_y = $year_data[$budget_year - 4] ?? null;
        $end_date = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->value('DATE_END');

        $today = Carbon::today()->toDateString(); // ได้รูปแบบ YYYY-MM-DD เช่น 2025-10-07
           if ($today > $end_date) {
                $calc_end_date = $end_date; // ถ้าเลยปีงบแล้วใช้วันสิ้นสุดปีงบ
            } else {
                $calc_end_date = $today; // ถ้ายังอยู่ในปีงบ ใช้วันปัจจุบัน
            }
        //คำนวณจำนวนวันตั้งแต่ต้นปีงบ (1 ต.ค.) ถึงวันปัจจุบัน
        $diff_days = Carbon::parse($start_date)->diffInDays(Carbon::parse($calc_end_date)) + 1;

        $total = DB::table('opd')
            ->whereBetween('vstdate', [$today, $today])
            ->selectRaw("
                COALESCE(SUM(visit_ppfs),0)             AS visit_ppfs,
                COALESCE(SUM(inc_ppfs),0)               AS inc_ppfs,   
                COALESCE(SUM(visit_ppfs_claim),0)       AS visit_ppfs_claim,
                COALESCE(SUM(inc_ppfs_claim),0)         AS inc_ppfs_claim,   
                COALESCE(SUM(inc_ppfs_receive),0)       AS inc_ppfs_receive,              
                COALESCE(SUM(visit_ucs_cr),0)           AS visit_ucs_cr,
                COALESCE(SUM(inc_uccr),0)               AS inc_uccr,
                COALESCE(SUM(visit_ucs_cr_claim),0)     AS visit_ucs_cr_claim,
                COALESCE(SUM(inc_uccr_claim),0)         AS inc_uccr_claim,
                COALESCE(SUM(inc_uccr_receive),0)       AS inc_uccr_receive,
                COALESCE(SUM(visit_ucs_herb),0)         AS visit_ucs_herb,
                COALESCE(SUM(inc_herb),0)               AS inc_herb,
                COALESCE(SUM(visit_ucs_herb_claim),0)   AS visit_ucs_herb_claim,
                COALESCE(SUM(inc_herb_claim),0)         AS inc_herb_claim,
                COALESCE(SUM(inc_herb_receive),0)       AS inc_herb_receive
               
            ")->first();

        // ส่งเป็น array ใช้ง่าย ๆ ใน Blade
        $card = [
            'visit_ppfs'            => (int)$total->visit_ppfs,
            'inc_ppfs'              => (int)$total->inc_ppfs,
            'visit_ppfs_claim'      => (int)$total->visit_ppfs_claim,           
            'inc_ppfs_claim'        => (int)$total->inc_ppfs_claim,
            'inc_ppfs_receive'      => (int)$total->inc_ppfs_receive,
            'visit_ucs_cr'          => (float)$total->visit_ucs_cr,
            'inc_uccr'              => (int)$total->inc_uccr,
            'visit_ucs_cr_claim'    => (float)$total->visit_ucs_cr_claim,  
            'inc_uccr_claim'        => (int)$total->inc_uccr_claim,         
            'inc_uccr_receive'      => (float)$total->inc_uccr_receive,
            'visit_ucs_herb'        => (int)$total->visit_ucs_herb,
            'inc_herb'              => (float)$total->inc_herb,
            'visit_ucs_herb_claim'  => (int)$total->visit_ucs_herb_claim,
            'inc_herb_claim'        => (float)$total->inc_herb_claim,
            'inc_herb_receive'      => (int)$total->inc_herb_receive,           
        ];

        $hospitalSummary = DB::table('opd')
            ->join('hospital_config', 'opd.hospcode', '=', 'hospital_config.hospcode')
            ->whereBetween('vstdate', [$today, $today])
            ->select(
                'opd.hospcode',
                'hospital_config.hospname',
                DB::raw('MAX(opd.updated_at) AS last_updated_at'),
                DB::raw('COALESCE(SUM(visit_ppfs),0) AS visit_ppfs'),
                DB::raw('COALESCE(SUM(inc_ppfs),0) AS inc_ppfs'),
                DB::raw('COALESCE(SUM(visit_ppfs_claim),0) AS visit_ppfs_claim'),
                DB::raw('COALESCE(SUM(inc_ppfs_claim),0) AS inc_ppfs_claim'),
                DB::raw('COALESCE(SUM(inc_ppfs_receive),0) AS inc_ppfs_receive'),
                DB::raw('COALESCE(SUM(visit_ucs_cr),0) AS visit_ucs_cr'),
                DB::raw('COALESCE(SUM(inc_uccr),0) AS inc_uccr'),
                DB::raw('COALESCE(SUM(visit_ucs_cr_claim),0) AS visit_ucs_cr_claim'),
                DB::raw('COALESCE(SUM(inc_uccr_claim),0) AS inc_uccr_claim'),
                DB::raw('COALESCE(SUM(inc_uccr_receive),0) AS inc_uccr_receive'),
                DB::raw('COALESCE(SUM(visit_ucs_herb),0) AS visit_ucs_herb'),
                DB::raw('COALESCE(SUM(inc_herb),0) AS inc_herb'),
                DB::raw('COALESCE(SUM(visit_ucs_herb_claim),0) AS visit_ucs_herb_claim'),
                DB::raw('COALESCE(SUM(inc_herb_claim),0) AS inc_herb_claim'),
                DB::raw('COALESCE(SUM(inc_herb_receive),0) AS inc_herb_receive'),
            )
            ->groupBy('opd.hospcode', 'hospital_config.hospname')
            ->orderBy('opd.hospcode')
            ->get();

        $update_at10985 = DB::table('opd')->where('hospcode', '10985')->max('updated_at');
        $update_at10986 = DB::table('opd')->where('hospcode', '10986')->max('updated_at');
        $update_at10987 = DB::table('opd')->where('hospcode', '10987')->max('updated_at');
        $update_at10988 = DB::table('opd')->where('hospcode', '10988')->max('updated_at');
        $update_at10989 = DB::table('opd')->where('hospcode', '10989')->max('updated_at');
        $update_at10990 = DB::table('opd')->where('hospcode', '10990')->max('updated_at');
        $update_at10703 = DB::table('opd')->where('hospcode', '10703')->max('updated_at');

// Claim------------------------------------------------------------------------------------------------------------------

        // === Helper Function สำหรับดึงข้อมูลตาม hospcode ===
        function getHospitalSummary($hospcode, $start_date, $end_date)
        {
            return collect(DB::select("
                SELECT MIN(CASE
                    WHEN MONTH(vstdate)=10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)=9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(vstdate)+543, 2))
                END) AS month, 
                SUM(visit_ppfs)             AS visit_ppfs,
                SUM(inc_ppfs)               AS inc_ppfs,
                SUM(visit_ppfs_claim)       AS visit_ppfs_claim,
                SUM(inc_ppfs_claim)         AS inc_ppfs_claim,
                SUM(inc_ppfs_receive)       AS inc_ppfs_receive,
                SUM(visit_ucs_cr)           AS visit_ucs_cr,
                SUM(inc_uccr)               AS inc_uccr,
                SUM(visit_ucs_cr_claim)     AS visit_ucs_cr_claim,
                SUM(inc_uccr_claim)         AS inc_uccr_claim,
                SUM(inc_uccr_receive)       AS inc_uccr_receive,
                SUM(visit_ucs_herb)         AS visit_ucs_herb,
                SUM(inc_herb)               AS inc_herb,
                SUM(visit_ucs_herb_claim)   AS visit_ucs_herb_claim,
                SUM(inc_herb_claim)         AS inc_herb_claim,
                SUM(inc_herb_receive)       AS inc_herb_receive          
                FROM opd
                WHERE vstdate BETWEEN ? AND ?
                AND hospcode = ?
                GROUP BY YEAR(vstdate), MONTH(vstdate)
                ORDER BY YEAR(vstdate), MONTH(vstdate)
            ", [$start_date, $end_date, $hospcode]));
        }

        // === เรียกใช้ ===
        $total_10985 = getHospitalSummary(10985, $start_date, $end_date);
        $total_10986 = getHospitalSummary(10986, $start_date, $end_date);
        $total_10987 = getHospitalSummary(10987, $start_date, $end_date);
        $total_10988 = getHospitalSummary(10988, $start_date, $end_date);
        $total_10989 = getHospitalSummary(10989, $start_date, $end_date);
        $total_10990 = getHospitalSummary(10990, $start_date, $end_date);
        $total_10703 = getHospitalSummary(10703, $start_date, $end_date);

        // === ส่งไปหน้า View ===
        return view('dashboard_claim', array_merge(
            $card,
            compact(
                'budget_year_select',
                'budget_year',
                'diff_days',
                'update_at10985', 'total_10985',
                'update_at10986', 'total_10986',
                'update_at10987', 'total_10987',
                'update_at10988', 'total_10988',
                'update_at10989', 'total_10989',
                'update_at10990', 'total_10990',
                'update_at10703', 'total_10703',
                'hospitalSummary'
            )
        ));
    }
}
