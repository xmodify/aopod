<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ipd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IpdController extends Controller
{
    public function ipd(Request $request)
    {
        // Auth: อนุญาตเฉพาะ user ที่เป็นโรงพยาบาลและมี ability: ingest
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate เฉพาะฟิลด์ที่ต้องใช้ เพื่อกัน nested array ทำให้เกิด error แปลกๆ
        $validated = $request->validate([
            'records' => ['required', 'array', 'min:1'],
            'records.*.dchdate' => ['required', 'date_format:Y-m-d'],

            // จำนวน visit (int)
            'records.*.an_total'            => ['required', 'integer', 'min:0'],
            'records.*.admdate'             => ['required', 'integer', 'min:0'],
           
            // รายได้ (float/double)
            'records.*.bed_occupancy'       => ['required', 'numeric', 'min:0'],
            'records.*.active_bed'          => ['required', 'numeric', 'min:0'],
            'records.*.cmi'                 => ['required', 'numeric', 'min:0'],            
            'records.*.adjrw'               => ['required', 'numeric', 'min:0'],
            'records.*.inc_total'           => ['required', 'numeric', 'min:0'],
            'records.*.inc_lab_total'       => ['required', 'numeric', 'min:0'],
            'records.*.inc_drug_total'      => ['required', 'numeric', 'min:0'],           
        ]);

        $hospcode = $hospital->hospcode;
        $rows = $validated['records'];

        // ---- เตรียมวันที่ทั้งหมดจาก payload ----
        $dates = collect($rows)->pluck('dchdate')->unique()->values();

        // ---- เช็ควันที่ที่มีอยู่แล้วใน DB (ของ hospcode นี้) ----
        $existing = Ipd::query()
            ->where('hospcode', $hospcode)
            ->whereIn('dchdate', $dates)
            ->pluck('dchdate')
            ->all();

        // สร้าง set แบบปลอดภัย (กัน array_flip error หากมีค่า non-scalar)
        $existingSet = [];
        foreach ($existing as $d) {
            if (is_string($d) || is_int($d)) {
                $existingSet[(string)$d] = true;
            }
        }

        // ---- กัน payload ซ้ำวันที่เดียวกัน: อันหลังทับอันแรก ----
        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['dchdate']] = $r;
        }

        // ---- แปลงเป็น rows สำหรับ upsert ----
        $now = now();
        $toUpsert = [];
        foreach ($byDate as $dchdate => $row) {
            $toUpsert[] = [
                'hospcode' => $hospcode,
                'dchdate'  => $dchdate,

                // Visits (int)
                'an_total'          => $row['an_total'],                
                'admdate'           => $row['admdate'],
                               
                // Income (float/double)
                'bed_occupancy'     => $row['bed_occupancy'],
                'active_bed'        => $row['active_bed'],                
                'cmi'               => $row['cmi'],
                'adjrw'             => $row['adjrw'],
                'inc_total'         => $row['inc_total'],
                'inc_lab_total'     => $row['inc_lab_total'],
                'inc_drug_total'    => $row['inc_drug_total'],
               
                // timestamps
                'updated_at' => $now,
            ];
        }

        // ---- นับผลลัพธ์ created / updated ให้ถูกต้อง ----
        $payloadDates  = array_keys($byDate);
        $existingDates = array_keys($existingSet);
        $created = count(array_diff($payloadDates, $existingDates));
        $updated = count(array_intersect($payloadDates, $existingDates));

        // ---- ทำ upsert ----
        if (!empty($toUpsert)) {
            DB::beginTransaction();
            try {
                DB::table((new Ipd())->getTable())->upsert(
                    $toUpsert,
                    ['hospcode', 'dchdate'], // unique keys
                    [
                        // Visits
                        'an_total', 'admdate',

                        // Incomes
                        'bed_occupancy', 'active_bed','cmi','adjrw',
                        'inc_total', 'inc_lab_total', 'inc_drug_total',                     

                        // Timestamp
                        'updated_at',
                    ]
                );

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                // หากล้มเหลวทั้งก้อน ให้ส่งรายละเอียด error กลับ
                return response()->json([
                    'hospcode' => $hospcode,
                    'created'  => 0,
                    'updated'  => 0,
                    'errors'   => [
                        ['message' => $e->getMessage()]
                    ],
                ], 500);
            }
        }

        // 200: สำเร็จทั้งหมด | 207: บางส่วน (ในที่นี้เรา validate ก่อนแล้ว ปกติจะ 200)
        return response()->json([
            'hospcode' => $hospcode,
            'created'  => $created,
            'updated'  => $updated,
            'errors'   => [],
        ], 200);
    }

    /**
     * (ทางเลือก) GET /api/op-insurance/health
     * ใช้เช็คว่า endpoint ใช้งานได้และ token ถูกต้องหรือไม่
     */
    public function health()
    {
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 403);
        }
        return response()->json(['ok' => true, 'hospcode' => $hospital->hospcode]);
    }

//############################################################################################################################ 
    public function get_ipd(Request $request)
    {
        $hospital = Auth::user();

        // ตรวจสิทธิ์ token
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $hospcode = $hospital->hospcode;

        // ✅ รายชื่อ hospcode ที่ดูได้ทุก hospcode
        $superHospcodes = ['00025']; // แก้ตามที่ต้องการ

        $start_date = $request->query('start_date') ?? Carbon::now()->subDays(10)->format('Y-m-d'); // 10 วันย้อนหลัง
        $end_date = $request->query('end_date') ?? Carbon::now()->format('Y-m-d'); // วันนี้
        $limit = $request->query('limit', 200);
        
        // ✅ เริ่มจาก query builder (ไม่ใช้ get() ทันที)
        $query = DB::table('ipd');


        // ✅ ถ้าไม่ใช่ super hospcode → เห็นเฉพาะของตัวเอง
        if (!in_array($hospcode, $superHospcodes)) {
            $query->where('hospcode', $hospcode);
        }

        // ✅ filter วันที่ (ถ้ามี)
        if ($start_date && $end_date) {
            $query->whereBetween('dchdate', [$start_date, $end_date]);
        }

        $data = $query->orderBy('dchdate', 'desc')->limit($limit)->get();

        return response()->json([
            'ok' => true,
            'hospcode' => $hospcode,
            'super' => in_array($hospcode, $superHospcodes),
            'count' => $data->count(),
            'data' => $data,
        ]);
    }

}
