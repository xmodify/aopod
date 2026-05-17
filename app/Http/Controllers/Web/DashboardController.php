<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\OpInsurance;
// ใช้ Carbon ของ Laravel เพื่อความยืดหยุ่น (แทน date('Y-m-d'))
use Carbon\Carbon;

class DashboardController extends Controller
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

        $update_at10985 = DB::table('ipd')->where('hospcode', '10985')->max('updated_at');
        $update_at10986 = DB::table('ipd')->where('hospcode', '10986')->max('updated_at');
        $update_at10987 = DB::table('ipd')->where('hospcode', '10987')->max('updated_at');
        $update_at10988 = DB::table('ipd')->where('hospcode', '10988')->max('updated_at');
        $update_at10989 = DB::table('ipd')->where('hospcode', '10989')->max('updated_at');
        $update_at10990 = DB::table('ipd')->where('hospcode', '10990')->max('updated_at');
        $update_at10703 = DB::table('ipd')->where('hospcode', '10703')->max('updated_at');

        // ดึงข้อมูลโรงพยาบาลทั้งหมด-----------------------------------------------------------------------------------
        $hospitals = DB::table('hospital_config')
            ->select('hospcode', 'hospname', 'bed_qty', 'bed_use','updated_at')
            ->get();

        // ข้อมูลเตียง-----------------------------------------------------------------------------------
        $ipd_bed_dep = DB::table('ipd_bed_dep as d')
            ->join('hospital_config as h', 'd.hospcode', '=', 'h.hospcode')
            ->select(
                'd.hospcode',
                'h.hospname',
                DB::raw('SUM(d.bed_qty) AS bed_qty'),
                DB::raw('SUM(d.bed_use) AS bed_use'),
                DB::raw('MAX(d.updated_at) AS updated_at')
            )
            ->groupBy('d.hospcode', 'h.hospname')
            ->orderBy('d.hospcode')
            ->get();

        // รวมยอดเตียงทั้งหมด
        $total_bed_qty = $ipd_bed_dep->sum('bed_qty') ?? 0;
        $total_bed_use = $ipd_bed_dep->sum('bed_use') ?? 0;
        $total_bed_empty = $total_bed_qty - $total_bed_use;    



        // ข้อมูลเตียงแยก รพ ----------------------------------------------------------------------------
        $bedData = [];
        foreach ($hospitals as $h) {
            $beds = DB::table('ipd_bed_dep as d')
                ->leftJoin('ipd_bed_type as t', 'd.bed_code', '=', 't.bed_code')
                ->where('d.hospcode', $h->hospcode)
                ->whereIn('d.bed_code', [
                    101100, 101201, 101301, 101400, 102201, 105208, 109602, 109604, 199100
                ])
                ->select(
                    'd.bed_code',
                    't.bed_name',
                    'd.bed_qty',
                    'd.bed_use',
                    DB::raw("
                        CASE 
                            WHEN d.bed_qty > 0 
                            THEN ROUND((d.bed_use / d.bed_qty) * 100, 2) 
                            ELSE 0 
                        END as bed_rate
                    ")
                )
                ->orderBy('t.bed_code')
                ->get();
            // เก็บข้อมูลแยกตาม hospcode
            $bedData[$h->hospcode] = [
                'hospname' => $h->hospname,
                'beds' => $beds
            ];
        }        

        // ดึงข้อมูลสรุป IPD แบบรายเดือน ตาม hospcode ที่กำหนด----------------------------------------------------
        function getIpdSummary($hospcode, $start_date, $end_date)
        {
            $sql = "
                SELECT  
                    CASE MONTH(i.dchdate)
                        WHEN 10 THEN CONCAT('ต.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 11 THEN CONCAT('พ.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 12 THEN CONCAT('ธ.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 1 THEN CONCAT('ม.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 2 THEN CONCAT('ก.พ. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 3 THEN CONCAT('มี.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 4 THEN CONCAT('เม.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 5 THEN CONCAT('พ.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 6 THEN CONCAT('มิ.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 7 THEN CONCAT('ก.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 8 THEN CONCAT('ส.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                        WHEN 9 THEN CONCAT('ก.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                    END AS month,
                    SUM(i.an_total) AS an_total,
                    SUM(i.admdate) AS admdate,
                    ROUND((SUM(i.admdate) * 100) / 
                        (h.bed_report *
                            CASE 
                                WHEN YEAR(i.dchdate)=YEAR(CURDATE()) 
                                AND MONTH(i.dchdate)=MONTH(CURDATE()) 
                                    THEN DAY(CURDATE())
                                ELSE DAY(LAST_DAY(i.dchdate))
                            END
                        ), 2
                    ) AS bed_occupancy,
                    ROUND( SUM(i.admdate) / 
                        CASE 
                            WHEN YEAR(i.dchdate)=YEAR(CURDATE()) 
                            AND MONTH(i.dchdate)=MONTH(CURDATE()) 
                                THEN DAY(CURDATE())
                            ELSE DAY(LAST_DAY(i.dchdate))
                        END
                    , 2 ) AS active_bed,
                    ROUND(SUM(i.adjrw), 4) AS adjrw,
                    ROUND(SUM(i.adjrw)/SUM(i.an_total), 2) AS cmi,
                    SUM(i.inc_total) AS inc_total ,
				    SUM(i.inc_lab_total) AS inc_lab_total ,
                    SUM(i.inc_drug_total) AS inc_drug_total 
                FROM ipd i
                LEFT JOIN hospital_config h ON h.hospcode=i.hospcode 
                WHERE i.dchdate BETWEEN ? AND ?
                AND i.hospcode = ?
                GROUP BY MONTH(i.dchdate)
                ORDER BY YEAR(i.dchdate), MONTH(i.dchdate)
            ";

             return collect(DB::select($sql, [$start_date, $end_date, $hospcode]));
        }
        // ✅ เรียกใช้ฟังก์ชัน (พร้อม collect() ครอบทุกตัว)
        $ipd_10985 = getIpdSummary(10985, $start_date, $end_date);
        $ipd_10986 = getIpdSummary(10986, $start_date, $end_date);
        $ipd_10987 = getIpdSummary(10987, $start_date, $end_date);
        $ipd_10988 = getIpdSummary(10988, $start_date, $end_date);
        $ipd_10989 = getIpdSummary(10989, $start_date, $end_date);
        $ipd_10990 = getIpdSummary(10990, $start_date, $end_date);
        $ipd_10703 = getIpdSummary(10703, $start_date, $end_date);
        // END ----------------------------------------------------------------------------------------------



        return view('dashboard', compact(
            'budget_year_select',
            'budget_year',
            'diff_days',
            'update_at10985','ipd_10985',
            'update_at10986','ipd_10986',
            'update_at10987','ipd_10987',
            'update_at10988','ipd_10988',
            'update_at10989','ipd_10989',
            'update_at10990','ipd_10990',
            'update_at10703','ipd_10703',
            'ipd_bed_dep',
            'total_bed_qty',
            'total_bed_use',
            'total_bed_empty',
            'hospitals',
            'bedData'
            )
        );
    }
//###############################################################################################################################
    public function bed_dep($hospcode)
    {
        $beds = DB::table('ipd_bed_dep as d')
            ->leftJoin('ipd_bed_type as t', 'd.bed_code', '=', 't.bed_code')
            ->where('d.hospcode', $hospcode)
            ->select(
                'd.bed_code',
                't.bed_name',
                't.unit',
                'd.bed_qty',
                'd.bed_use',
                DB::raw('
                    CASE 
                        WHEN d.bed_qty > 0 
                        THEN ROUND((d.bed_use / d.bed_qty) * 100, 2) 
                        ELSE 0 
                    END as bed_rate
                ')
            )
            ->orderBy('t.bed_name')
            ->get();

        // รวมผล
        $sum_bed_qty = $beds->sum('bed_qty');
        $sum_bed_use = $beds->sum('bed_use');
        $sum_bed_empty = $sum_bed_qty - $sum_bed_use;
        $sum_rate = $sum_bed_qty > 0 ? round(($sum_bed_use / $sum_bed_qty) * 100, 2) : 0;

        return response()->json([
            'beds' => $beds,
            'summary' => [
                'total' => $sum_bed_qty,
                'used'  => $sum_bed_use,
                'empty' => $sum_bed_empty,
                'rate'  => $sum_rate
            ]
        ]);
    }
    
}
