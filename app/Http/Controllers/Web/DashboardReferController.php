<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardReferController extends Controller
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

        $today = Carbon::today()->toDateString(); 
        if ($today > $end_date) {
            $calc_end_date = $end_date; 
        } else {
            $calc_end_date = $today; 
        }
        $diff_days = Carbon::parse($start_date)->diffInDays(Carbon::parse($calc_end_date)) + 1;

        $update_at10985 = DB::table('opd')->where('hospcode', '10985')->max('updated_at');
        $update_at10986 = DB::table('opd')->where('hospcode', '10986')->max('updated_at');
        $update_at10987 = DB::table('opd')->where('hospcode', '10987')->max('updated_at');
        $update_at10988 = DB::table('opd')->where('hospcode', '10988')->max('updated_at');
        $update_at10989 = DB::table('opd')->where('hospcode', '10989')->max('updated_at');
        $update_at10990 = DB::table('opd')->where('hospcode', '10990')->max('updated_at');
        $update_at10703 = DB::table('opd')->where('hospcode', '10703')->max('updated_at');

        $total = DB::table('opd')
            ->whereBetween('vstdate', [$today, $today])
            ->selectRaw("                
                COALESCE(SUM(visit_referout_inprov),0)          AS visit_referout_inprov,
                COALESCE(SUM(visit_referout_inprov_ipd),0)      AS visit_referout_inprov_ipd,
                COALESCE(SUM(visit_referout_outprov),0)         AS visit_referout_outprov,
                COALESCE(SUM(visit_referout_outprov_ipd),0)     AS visit_referout_outprov_ipd,
                COALESCE(SUM(visit_referin_inprov),0)           AS visit_referin_inprov,
                COALESCE(SUM(visit_referin_outprov),0)          AS visit_referin_outprov,
                COALESCE(SUM(visit_referin_inprov_ipd),0)       AS visit_referin_inprov_ipd,
                COALESCE(SUM(visit_referin_outprov_ipd),0)      AS visit_referin_outprov_ipd,
                COALESCE(SUM(visit_referback_inprov),0)         AS visit_referback_inprov,
                COALESCE(SUM(visit_referback_outprov),0)        AS visit_referback_outprov 
            ")->first();

        $card = [           
            'visit_referout_inprov'         => (int)$total->visit_referout_inprov, 
            'visit_referout_inprov_ipd'     => (int)$total->visit_referout_inprov_ipd, 
            'visit_referout_outprov'        => (int)$total->visit_referout_outprov, 
            'visit_referout_outprov_ipd'    => (int)$total->visit_referout_outprov_ipd, 
            'visit_referin_inprov'          => (int)$total->visit_referin_inprov, 
            'visit_referin_outprov'         => (int)$total->visit_referin_outprov, 
            'visit_referin_inprov_ipd'      => (int)$total->visit_referin_inprov_ipd, 
            'visit_referin_outprov_ipd'     => (int)$total->visit_referin_outprov_ipd, 
            'visit_referback_inprov'        => (int)$total->visit_referback_inprov, 
            'visit_referback_outprov'       => (int)$total->visit_referback_outprov,
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
                DB::raw('COALESCE(SUM(opd.visit_referout_inprov),0) AS visit_referout_inprov'),
                DB::raw('COALESCE(SUM(opd.visit_referout_outprov),0) AS visit_referout_outprov'),
                DB::raw('COALESCE(SUM(opd.visit_referout_inprov_ipd),0) AS visit_referout_inprov_ipd'),                
                DB::raw('COALESCE(SUM(opd.visit_referout_outprov_ipd),0) AS visit_referout_outprov_ipd'),
                DB::raw('COALESCE(SUM(opd.visit_referin_inprov),0) AS visit_referin_inprov'),
                DB::raw('COALESCE(SUM(opd.visit_referin_outprov),0) AS visit_referin_outprov'),
                DB::raw('COALESCE(SUM(opd.visit_referin_inprov_ipd),0) AS visit_referin_inprov_ipd'),
                DB::raw('COALESCE(SUM(opd.visit_referin_outprov_ipd),0) AS visit_referin_outprov_ipd'),
                DB::raw('COALESCE(SUM(opd.visit_referback_inprov),0) AS visit_referback_inprov'),
                DB::raw('COALESCE(SUM(opd.visit_referback_outprov),0) AS visit_referback_outprov')            
            )
            ->groupBy('hospital_config.hospcode', 'hospital_config.hospname')
            ->orderBy('hospital_config.hospcode')
            ->get();

        function getReferSummary($hospcode, $start_date, $end_date)
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
                SUM(visit_referout_inprov)          AS visit_referout_inprov,
                SUM(visit_referout_outprov)         AS visit_referout_outprov,
                SUM(visit_referout_inprov_ipd)      AS visit_referout_inprov_ipd,
                SUM(visit_referout_outprov_ipd)     AS visit_referout_outprov_ipd,
                SUM(visit_referin_inprov)           AS visit_referin_inprov,
                SUM(visit_referin_outprov)          AS visit_referin_outprov,
                SUM(visit_referin_inprov_ipd)       AS visit_referin_inprov_ipd,
                SUM(visit_referin_outprov_ipd)      AS visit_referin_outprov_ipd,
                SUM(visit_referback_inprov)         AS visit_referback_inprov,
                SUM(visit_referback_outprov)        AS visit_referback_outprov
                FROM opd
                WHERE vstdate BETWEEN ? AND ?
                AND hospcode = ?
                GROUP BY YEAR(vstdate), MONTH(vstdate)
                ORDER BY YEAR(vstdate), MONTH(vstdate)
            ";
             return collect(DB::select($sql, [$start_date, $end_date, $hospcode]));
        }
        
        $refer_10985 = getReferSummary(10985, $start_date, $end_date);
        $refer_10986 = getReferSummary(10986, $start_date, $end_date);
        $refer_10987 = getReferSummary(10987, $start_date, $end_date);
        $refer_10988 = getReferSummary(10988, $start_date, $end_date);
        $refer_10989 = getReferSummary(10989, $start_date, $end_date);
        $refer_10990 = getReferSummary(10990, $start_date, $end_date);
        $refer_10703 = getReferSummary(10703, $start_date, $end_date);

        return view('dashboard_refer', array_merge(
            $card,
            compact(
            'budget_year_select',
            'budget_year',
            'diff_days',
            'update_at10985','refer_10985',
            'update_at10986','refer_10986',
            'update_at10987','refer_10987',
            'update_at10988','refer_10988',
            'update_at10989','refer_10989',
            'update_at10990','refer_10990',
            'update_at10703','refer_10703',
            'hospitalSummary'
            )
        ));
    }
}
