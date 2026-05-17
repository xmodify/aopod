<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

// ใช้ Carbon ของ Laravel เพื่อความยืดหยุ่น (แทน date('Y-m-d'))
use Carbon\Carbon;

class DashboardOPDController extends Controller
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
                COALESCE(SUM(visit_total),0)                AS visit_total,
                COALESCE(SUM(visit_total_op),0)             AS visit_total_op,
                COALESCE(SUM(visit_total_pp),0)             AS visit_total_pp,
                COALESCE(SUM(visit_endpoint),0)             AS visit_endpoint, 
                COALESCE(SUM(visit_healthmed),0)            AS visit_healthmed, 
                COALESCE(SUM(visit_dent),0)                 AS visit_dent, 
                COALESCE(SUM(visit_physic),0)               AS visit_physic, 
                COALESCE(SUM(visit_anc),0)                  AS visit_anc, 
                COALESCE(SUM(visit_telehealth),0)           AS visit_telehealth, 
                COALESCE(SUM(visit_moph_oapp_booking),0)    AS visit_moph_oapp_booking, 
                COALESCE(SUM(visit_moph_oapp),0)            AS visit_moph_oapp, 
                COALESCE(SUM(visit_ucs_incup),0)            AS visit_ucs_incup, 
                COALESCE(SUM(visit_ucs_outprov),0)          AS visit_ucs_outprov,
                COALESCE(SUM(visit_ofc),0)                  AS visit_ofc,
                COALESCE(SUM(visit_lgo),0)                  AS visit_lgo,
                COALESCE(SUM(visit_sss),0)                  AS visit_sss,
                COALESCE(SUM(visit_pay),0)                  AS visit_pay
            ")->first();

        // ส่งเป็น array ใช้ง่าย ๆ ใน Blade
        $card = [
            'visit_total'                   => (int)$total->visit_total,
            'visit_total_op'                => (int)$total->visit_total_op,
            'visit_total_pp'                => (int)$total->visit_total_pp,           
            'visit_endpoint'                => (int)$total->visit_endpoint, 
            'visit_healthmed'               => (int)$total->visit_healthmed, 
            'visit_dent'                    => (int)$total->visit_dent, 
            'visit_physic'                  => (int)$total->visit_physic, 
            'visit_anc'                     => (int)$total->visit_anc, 
            'visit_telehealth'              => (int)$total->visit_telehealth, 
            'visit_moph_oapp_booking'       => (int)$total->visit_moph_oapp_booking,
            'visit_moph_oapp'               => (int)$total->visit_moph_oapp,
            'visit_ucs_incup'               => (int)$total->visit_ucs_incup,
            'visit_ucs_outprov'             => (int)$total->visit_ucs_outprov,
            'visit_ofc'                     => (int)$total->visit_ofc,            
            'visit_lgo'                     => (int)$total->visit_lgo,           
            'visit_sss'                     => (int)$total->visit_sss,           
            'visit_pay'                     => (int)$total->visit_pay,          
        ];

        $hospitalSummary = DB::table('hospital_config')
            ->leftJoin('opd', function($join) use ($today) {
                $join->on('hospital_config.hospcode', '=', 'opd.hospcode')
                     ->whereBetween('opd.vstdate', [$today, $today]);
            })
            ->select(
                'hospital_config.hospcode',
                'hospital_config.hospname',
                DB::raw('(SELECT MAX(updated_at) FROM opd WHERE opd.hospcode = hospital_config.hospcode) AS last_updated_at'),
                DB::raw('COALESCE(SUM(opd.visit_total),0) AS visit_total'),
                DB::raw('COALESCE(SUM(opd.visit_total_op),0) AS visit_total_op'),
                DB::raw('COALESCE(SUM(opd.visit_total_pp),0) AS visit_total_pp'),
                DB::raw('COALESCE(SUM(opd.visit_endpoint),0) AS visit_endpoint'),   
                DB::raw('COALESCE(SUM(opd.visit_healthmed),0) AS visit_healthmed'),   
                DB::raw('COALESCE(SUM(opd.visit_dent),0) AS visit_dent'),   
                DB::raw('COALESCE(SUM(opd.visit_physic),0) AS visit_physic'),   
                DB::raw('COALESCE(SUM(opd.visit_anc),0) AS visit_anc'),   
                DB::raw('COALESCE(SUM(opd.visit_telehealth),0) AS visit_telehealth'),
                DB::raw('COALESCE(SUM(opd.visit_moph_oapp_booking),0) AS visit_moph_oapp_booking'), 
                DB::raw('COALESCE(SUM(opd.visit_moph_oapp),0) AS visit_moph_oapp'), 
                DB::raw('COALESCE(SUM(opd.visit_ucs_incup),0)+COALESCE(SUM(opd.visit_ucs_inprov),0)+COALESCE(SUM(opd.visit_ucs_outprov),0) AS visit_ucs'),
                DB::raw('COALESCE(SUM(opd.inc_ucs_incup),0)+COALESCE(SUM(opd.inc_ucs_inprov),0)+COALESCE(SUM(opd.inc_ucs_outprov),0) AS inc_ucs'),
                DB::raw('COALESCE(SUM(opd.visit_ucs_incup),0) AS visit_ucs_incup'),
                DB::raw('COALESCE(SUM(opd.inc_ucs_incup),0) AS inc_ucs_incup'),
                DB::raw('COALESCE(SUM(opd.visit_ucs_inprov),0) AS visit_ucs_inprov'),
                DB::raw('COALESCE(SUM(opd.inc_ucs_inprov),0) AS inc_ucs_inprov'),
                DB::raw('COALESCE(SUM(opd.visit_ofc),0) AS visit_ofc'),
                DB::raw('COALESCE(SUM(opd.inc_ofc),0) AS inc_ofc'),
                DB::raw('COALESCE(SUM(opd.visit_lgo),0) AS visit_lgo'),
                DB::raw('COALESCE(SUM(opd.inc_lgo),0) AS inc_lgo'),
                DB::raw('COALESCE(SUM(opd.visit_sss),0) AS visit_sss'),
                DB::raw('COALESCE(SUM(opd.inc_sss),0) AS inc_sss'),
                DB::raw('COALESCE(SUM(opd.visit_pay),0) AS visit_pay'),
                DB::raw('COALESCE(SUM(opd.inc_pay),0) AS inc_pay')
            )
            ->groupBy('hospital_config.hospcode', 'hospital_config.hospname')
            ->orderBy('hospital_config.hospcode')
            ->get();

        $update_at10985 = DB::table('opd')->where('hospcode', '10985')->max('updated_at');
        $update_at10986 = DB::table('opd')->where('hospcode', '10986')->max('updated_at');
        $update_at10987 = DB::table('opd')->where('hospcode', '10987')->max('updated_at');
        $update_at10988 = DB::table('opd')->where('hospcode', '10988')->max('updated_at');
        $update_at10989 = DB::table('opd')->where('hospcode', '10989')->max('updated_at');
        $update_at10990 = DB::table('opd')->where('hospcode', '10990')->max('updated_at');
        $update_at10703 = DB::table('opd')->where('hospcode', '10703')->max('updated_at');

// OPD------------------------------------------------------------------------------------------------------------------

        // ✅ สร้างฟังก์ชันดึงข้อมูลแต่ละ รพ.
        function getHospitalOpdSummary($hospcode, $start_date, $end_date)
        {
            $sql = "
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
                SUM(hn_total)                   AS hn_total,
                SUM(visit_total)                AS visit_total,
                SUM(visit_total_op)             AS visit_total_op,
                SUM(visit_total_pp)             AS visit_total_pp,
                SUM(visit_healthmed)            AS visit_healthmed,
                SUM(visit_dent)                 AS visit_dent,
                SUM(visit_physic)               AS visit_physic,
                SUM(visit_anc)                  AS visit_anc,
                SUM(visit_telehealth)           AS visit_telehealth,
                SUM(visit_moph_oapp_booking)    AS visit_moph_oapp_booking,
                SUM(visit_moph_oapp)            AS visit_moph_oapp,
                SUM(visit_ucs_incup)            AS visit_ucs_incup,
                SUM(visit_ucs_inprov)           AS visit_ucs_inprov,
                SUM(visit_ucs_outprov)          AS visit_ucs_outprov,
                SUM(visit_ofc)                  AS visit_ofc,
                SUM(visit_bkk)                  AS visit_bkk,
                SUM(visit_bmt)                  AS visit_bmt,
                SUM(visit_sss)                  AS visit_sss,
                SUM(visit_lgo)                  AS visit_lgo,
                SUM(visit_fss)                  AS visit_fss,
                SUM(visit_stp)                  AS visit_stp,
                SUM(visit_pay)                  AS visit_pay,
                SUM(inc_total)                  AS inc_total,
                SUM(inc_lab_total)              AS inc_lab_total,
                SUM(inc_drug_total)             AS inc_drug_total,
                SUM(inc_ucs_incup)              AS inc_ucs_incup,
                SUM(inc_lab_ucs_incup)          AS inc_lab_ucs_incup,
                SUM(inc_drug_ucs_incup)         AS inc_drug_ucs_incup,
                SUM(inc_ucs_inprov)             AS inc_ucs_inprov,
                SUM(inc_lab_ucs_inprov)         AS inc_lab_ucs_inprov,
                SUM(inc_drug_ucs_inprov)        AS inc_drug_ucs_inprov,
                SUM(inc_ucs_outprov)            AS inc_ucs_outprov,
                SUM(inc_lab_ucs_outprov)        AS inc_lab_ucs_outprov,
                SUM(inc_drug_ucs_outprov)       AS inc_drug_ucs_outprov,
                SUM(inc_ofc)                    AS inc_ofc,
                SUM(inc_lab_ofc)                AS inc_lab_ofc,
                SUM(inc_drug_ofc)               AS inc_drug_ofc,
                SUM(inc_bkk)                    AS inc_bkk,
                SUM(inc_lab_bkk)                AS inc_lab_bkk,
                SUM(inc_drug_bkk)               AS inc_drug_bkk,
                SUM(inc_bmt)                    AS inc_bmt,
                SUM(inc_lab_bmt)                AS inc_lab_bmt,
                SUM(inc_drug_bmt)               AS inc_drug_bmt,
                SUM(inc_sss)                    AS inc_sss,
                SUM(inc_lab_sss)                AS inc_lab_sss,
                SUM(inc_drug_sss)               AS inc_drug_sss,
                SUM(inc_lgo)                    AS inc_lgo,
                SUM(inc_lab_lgo)                AS inc_lab_lgo,
                SUM(inc_drug_lgo)               AS inc_drug_lgo,
                SUM(inc_fss)                    AS inc_fss,
                SUM(inc_lab_fss)                AS inc_lab_fss,
                SUM(inc_drug_fss)               AS inc_drug_fss,
                SUM(inc_stp)                    AS inc_stp,
                SUM(inc_lab_stp)                AS inc_lab_stp,
                SUM(inc_drug_stp)               AS inc_drug_stp,
                SUM(inc_pay)                    AS inc_pay,
                SUM(inc_lab_pay)                AS inc_lab_pay,
                SUM(inc_drug_pay)               AS inc_drug_pay
                FROM opd
                WHERE vstdate BETWEEN ? AND ?
                AND hospcode = ?
                GROUP BY YEAR(vstdate), MONTH(vstdate)
                ORDER BY YEAR(vstdate), MONTH(vstdate)
            ";
            
            return collect(DB::select($sql, [$start_date, $end_date, $hospcode]));
        }

        // ✅ เรียกใช้ฟังก์ชัน (พร้อม collect() ครอบทุกตัว)
        $total_10985 = getHospitalOpdSummary(10985, $start_date, $end_date);
        $total_10986 = getHospitalOpdSummary(10986, $start_date, $end_date);
        $total_10987 = getHospitalOpdSummary(10987, $start_date, $end_date);
        $total_10988 = getHospitalOpdSummary(10988, $start_date, $end_date);
        $total_10989 = getHospitalOpdSummary(10989, $start_date, $end_date);
        $total_10990 = getHospitalOpdSummary(10990, $start_date, $end_date);
        $total_10703 = getHospitalOpdSummary(10703, $start_date, $end_date);

        // ✅ ส่งไปหน้า view
        return view('dashboard_opd', array_merge(
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
