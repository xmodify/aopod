<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Birth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BirthDataController extends Controller
{
    /**
     * Display birth data list.
     */
    public function index(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessBirth()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        $births = Birth::orderBy('id', 'desc')->get();

        // Calculate calendar years in B.E. (byear)
        $fiscalYears = Birth::selectRaw('DISTINCT byear as fiscal_year')
            ->whereNotNull('byear')
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year')
            ->toArray();

        // Default selected year is latest available or current B.E. year
        $currentYearBE = date('Y') + 543;
        $defaultFiscalYear = $currentYearBE;

        $selectedYear = $request->input('fiscal_year', reset($fiscalYears) ?: $defaultFiscalYear);

        // Fetch births in the selected calendar year
        $birthsInYear = Birth::where('byear', $selectedYear)->get();

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

        foreach ($birthsInYear as $birth) {
            $m = (int)$birth->bmon;
            if (!in_array($m, $monthsOrder)) continue;

            $prov = str_pad(trim((string)$birth->prov), 2, '0', STR_PAD_LEFT);
            $amp = str_pad(trim((string)$birth->amp), 2, '0', STR_PAD_LEFT);
            $districtCode = $prov . $amp;

            if (in_array($districtCode, $districts)) {
                $chartData[$m][$districtCode]++;
            }
        }

        return view('admin.birth.index', compact('births', 'fiscalYears', 'selectedYear', 'chartData', 'districtNames'));
    }

    /**
     * Import Excel birth data.
     */
    public function import(Request $request)
    {
        if (!auth()->check() || !auth()->user()->canAccessBirth()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        try {
            $file = $request->file('file');
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

                $mappedData[] = [
                    'prov' => isset($data['prov']) ? trim($data['prov']) : null,
                    'amp' => isset($data['amp']) ? trim($data['amp']) : null,
                    'tb' => isset($data['tb']) ? trim($data['tb']) : null,
                    'sex' => isset($data['sex']) ? trim($data['sex']) : null,
                    'byear' => isset($data['byear']) && is_numeric($data['byear']) ? (int)$data['byear'] : null,
                    'bmon' => isset($data['bmon']) && is_numeric($data['bmon']) ? (int)$data['bmon'] : null,
                    'bdate' => isset($data['bdate']) && is_numeric($data['bdate']) ? (int)$data['bdate'] : null,
                    'nat' => isset($data['nat']) ? trim($data['nat']) : null,
                    'no' => isset($data['no']) && is_numeric($data['no']) ? (int)$data['no'] : null,
                    'weight' => isset($data['weight']) && is_numeric($data['weight']) ? (int)$data['weight'] : null,
                    'mage' => isset($data['mage']) && is_numeric($data['mage']) ? (int)$data['mage'] : null,
                    'ket' => isset($data['ket']) ? trim($data['ket']) : null,
                    'yptell' => isset($data['yptell']) && is_numeric($data['yptell']) ? (int)$data['yptell'] : null,
                    'mptell' => isset($data['mptell']) && is_numeric($data['mptell']) ? (int)$data['mptell'] : null,
                    'dptell' => isset($data['dptell']) && is_numeric($data['dptell']) ? (int)$data['dptell'] : null,
                    'maddr' => isset($data['maddr']) ? trim($data['maddr']) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($mappedData)) {
                return response()->json(['success' => false, 'message' => 'ไม่พบแถวข้อมูลที่ถูกต้อง'], 400);
            }

            DB::transaction(function () use ($mappedData) {
                // Delete existing records matching imported no/byear to prevent duplicates
                $nos = array_filter(array_column($mappedData, 'no'));
                $years = array_filter(array_column($mappedData, 'byear'));
                if (!empty($nos) && !empty($years)) {
                    Birth::whereIn('no', $nos)->whereIn('byear', $years)->delete();
                }

                // Chunk insert records
                foreach (array_chunk($mappedData, 500) as $chunk) {
                    Birth::insert($chunk);
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
