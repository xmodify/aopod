<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Death;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DeathDataController extends Controller
{
    /**
     * Display death data list.
     */
    public function index(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessDeath()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $deaths = Death::orderBy('id', 'desc')->get();
        $hospitals = \App\Models\Hospital::where('is_active', true)->where('hospcode', '!=', '00025')->get();

        // Calculate calendar years in B.E. (dyear)
        $fiscalYears = Death::selectRaw('DISTINCT dyear as fiscal_year')
            ->whereNotNull('dyear')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year')
            ->toArray();

        // Default selected year is latest available or current B.E. year
        $currentYearBE = date('Y') + 543;
        $defaultFiscalYear = $currentYearBE;

        $selectedYear = $request->input('fiscal_year', reset($fiscalYears) ?: $defaultFiscalYear);

        // Fetch deaths in the selected calendar year
        $deathsInYear = Death::where('dyear', $selectedYear)->get();

        // Initialize monthly data for Amnat Charoen districts (3701 - 3707) starting from January to December
        $monthsOrder = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $districts = ['3701', '3702', '3703', '3704', '3705', '3706', '3707'];
        $districtNames = [
            '3701' => 'อ.เมืองอำนาจเจริญ',
            '3702' => 'อ.ชานุมาน',
            '3703' => 'อ.ปทุมราชวงศา',
            '3704' => 'อ.พนา',
            '3705' => 'อ.เสนางคนิคม',
            '3706' => 'อ.หัวตะพาน',
            '3707' => 'อ.ลืออำนาจ',
        ];

        $chartData = [];
        foreach ($monthsOrder as $m) {
            $chartData[$m] = array_fill_keys($districts, 0);
        }

        foreach ($deathsInYear as $death) {
            $m = (int)$death->dmon;
            if (!in_array($m, $monthsOrder)) continue;

            $districtCode = substr($death->lccaattmm, 0, 4);
            if (in_array($districtCode, $districts)) {
                $chartData[$m][$districtCode]++;
            }
        }

        return view('admin.death.index', compact('deaths', 'hospitals', 'fiscalYears', 'selectedYear', 'chartData', 'districtNames'));
    }

    /**
     * Import Excel death data.
     */
    public function import(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessDeath()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        try {
            $file = $request->file('file');
            if (!$file) {
                return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์ที่อัปโหลด กรุณาตรวจสอบขนาดไฟล์ไม่เกินขีดจำกัดของเซิร์ฟเวอร์'], 400);
            }
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json(['success' => false, 'message' => 'กรุณาอัปโหลดไฟล์ Excel (.xlsx, .xls) หรือ CSV เท่านั้น'], 400);
            }
            $spreadsheet = IOFactory::load($file->getRealPath());
            
            // Try to find Sheet2 as requested, fallback to active sheet
            $sheet = $spreadsheet->getSheetByName('Sheet2') ?? $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูลที่สามารถนำเข้าได้ในไฟล์ Excel'], 400);
            }

            // Map headers to column indices
            $headers = array_map('trim', array_map('strtoupper', $rows[0]));
            
            $mappedData = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row) || !isset($row[0]) || $row[0] === null) {
                    continue;
                }

                $data = [];
                foreach ($headers as $index => $headerName) {
                    if (empty($headerName)) {
                        continue;
                    }
                    $data[strtolower($headerName)] = $row[$index] ?? null;
                }

                // If PID is empty, skip
                if (empty($data['pid'])) {
                    continue;
                }

                $mappedData[] = [
                    'pid' => trim($data['pid']),
                    'sex' => isset($data['sex']) ? trim($data['sex']) : null,
                    'age' => isset($data['age']) && is_numeric($data['age']) ? (int)$data['age'] : null,
                    'ddate' => isset($data['ddate']) && is_numeric($data['ddate']) ? (int)$data['ddate'] : null,
                    'dmon' => isset($data['dmon']) && is_numeric($data['dmon']) ? (int)$data['dmon'] : null,
                    'dyear' => isset($data['dyear']) && is_numeric($data['dyear']) ? (int)$data['dyear'] : null,
                    'drcode' => isset($data['drcode']) ? trim($data['drcode']) : null,
                    'hos_id' => isset($data['hos_id']) ? trim($data['hos_id']) : null,
                    'lccaattmm' => isset($data['lccaattmm']) ? trim($data['lccaattmm']) : null,
                    'ncause' => isset($data['ncause']) ? trim($data['ncause']) : null,
                    'bdate' => isset($data['bdate']) && is_numeric($data['bdate']) ? (int)$data['bdate'] : null,
                    'bmon' => isset($data['bmon']) && is_numeric($data['bmon']) ? (int)$data['bmon'] : null,
                    'byear' => isset($data['byear']) && is_numeric($data['byear']) ? (int)$data['byear'] : null,
                    'dplace' => isset($data['dplace']) ? trim($data['dplace']) : null,
                    'ghos' => isset($data['ghos']) ? trim($data['ghos']) : null,
                    'codepro' => isset($data['codepro']) ? trim($data['codepro']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($mappedData)) {
                return response()->json(['success' => false, 'message' => 'ไม่พบแถวข้อมูลที่มีค่า PID ที่ถูกต้อง'], 400);
            }

            DB::transaction(function () use ($mappedData) {
                // Delete existing records matching imported PIDs to prevent duplicate entry issues
                $pids = array_column($mappedData, 'pid');
                Death::whereIn('pid', $pids)->delete();

                // Chunk insert records
                foreach (array_chunk($mappedData, 500) as $chunk) {
                    Death::insert($chunk);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'นำเข้าข้อมูลเรียบร้อยแล้วทั้งหมด ' . count($mappedData) . ' รายการ'
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล: ' . $e->getMessage()
            ], 500);
        }
    }
}
