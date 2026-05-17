<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardOperationController extends Controller
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
                COALESCE(SUM(visit_operation),0) AS visit_operation 
            ")->first();

        $card = [           
            'visit_operation' => (int)$total->visit_operation,
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
                DB::raw('COALESCE(SUM(opd.visit_operation), 0) AS visit_operation')
            )
            ->groupBy('hospital_config.hospcode', 'hospital_config.hospname')
            ->orderBy('hospital_config.hospcode')
            ->get();

        function getOperationSummary($hospcode, $start_date, $end_date)
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
                SUM(visit_operation) AS visit_operation
                FROM opd
                WHERE vstdate BETWEEN ? AND ?
                AND hospcode = ?
                GROUP BY YEAR(vstdate), MONTH(vstdate)
                ORDER BY YEAR(vstdate), MONTH(vstdate)
            ";
             return collect(DB::select($sql, [$start_date, $end_date, $hospcode]));
        }
        
        $operation_10985 = getOperationSummary(10985, $start_date, $end_date);
        $operation_10986 = getOperationSummary(10986, $start_date, $end_date);
        $operation_10987 = getOperationSummary(10987, $start_date, $end_date);
        $operation_10988 = getOperationSummary(10988, $start_date, $end_date);
        $operation_10989 = getOperationSummary(10989, $start_date, $end_date);
        $operation_10990 = getOperationSummary(10990, $start_date, $end_date);
        $operation_10703 = getOperationSummary(10703, $start_date, $end_date);

        return view('dashboard_operation', array_merge(
            $card,
            compact(
            'budget_year_select',
            'budget_year',
            'diff_days',
            'hospitalSummary',
            'update_at10985','operation_10985',
            'update_at10986','operation_10986',
            'update_at10987','operation_10987',
            'update_at10988','operation_10988',
            'update_at10989','operation_10989',
            'update_at10990','operation_10990',
            'update_at10703','operation_10703'
            )
        ));
    }
}
