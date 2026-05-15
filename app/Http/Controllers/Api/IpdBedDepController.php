<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\IpdBedDep;
use Carbon\Carbon;

class IpdBedDepController extends Controller
{
public function ingest(Request $request)
    {
        $hospital = Auth::user();

        // 🔐 ตรวจ token
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // ✅ ตรวจสอบ payload
        $validated = $request->validate([
            'records' => ['required', 'array', 'min:1'],
            'records.*.bed_code' => ['required', 'string', 'max:10'],
            'records.*.bed_qty'  => ['required', 'integer', 'min:0'],
            'records.*.bed_use'  => ['required', 'integer', 'min:0'],
        ]);

        $hospcode = $hospital->hospcode;
        $rows = $validated['records'];
        $now = now();

        // 🧩 เตรียมข้อมูลใหม่จาก payload
        $toUpsert = [];
        foreach ($rows as $r) {
            $toUpsert[] = [
                'hospcode'   => $hospcode,
                'bed_code'   => $r['bed_code'],
                'bed_qty'    => $r['bed_qty'],
                'bed_use'    => $r['bed_use'],
                'updated_at' => $now,
            ];
        }

        try {
            DB::beginTransaction();

            // 🔎 ดึงรายการ bed_code เดิมของ hospcode นี้
            $existingCodes = DB::table('ipd_bed_dep')
                ->where('hospcode', $hospcode)
                ->pluck('bed_code')
                ->toArray();

            $incomingCodes = array_column($toUpsert, 'bed_code');

            // 🗑️ หารายการที่มีอยู่ใน DB แต่ไม่มีใน payload → ต้องลบออก
            $toDelete = array_diff($existingCodes, $incomingCodes);

            if (!empty($toDelete)) {
                DB::table('ipd_bed_dep')
                    ->where('hospcode', $hospcode)
                    ->whereIn('bed_code', $toDelete)
                    ->delete();
            }

            // 💾 upsert (insert ใหม่ หรือ update ถ้ามีอยู่แล้ว)
            IpdBedDep::upsert(
                $toUpsert,
                ['hospcode', 'bed_code'],
                ['bed_qty', 'bed_use', 'updated_at']
            );

            DB::commit();

            return response()->json([
                'hospcode' => $hospcode,
                'count'    => count($toUpsert),
                'deleted'  => count($toDelete),
                'message'  => 'Success (upsert + cleanup)',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Database Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
//############################################################################################################################
    public function get(Request $request)
    {
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $hospcode = $hospital->hospcode;
        $superHospcodes = ['00025']; // ผู้ดูแลพิเศษ

        $query = DB::table('ipd_bed_dep');
        if (!in_array($hospcode, $superHospcodes)) {
            $query->where('hospcode', $hospcode);
        }

        $data = $query->orderBy('bed_code')->get();

        return response()->json([
            'ok' => true,
            'hospcode' => $hospcode,
            'super' => in_array($hospcode, $superHospcodes),
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
