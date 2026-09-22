<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReferController extends Controller
{
    public function refer(Request $request)
    {
        // Auth: อนุญาตเฉพาะ user ที่เป็นโรงพยาบาลและมี ability: ingest
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate
        $validated = $request->validate([
            'records' => ['required', 'array', 'min:1'],
            'records.*.vstdate' => ['required', 'date_format:Y-m-d'],
            'records.*.visit_referout_inprov'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referout_outprov'      => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referout_inprov_ipd'   => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referout_outprov_ipd'  => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referin_inprov'        => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referin_outprov'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referin_inprov_ipd'    => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referin_outprov_ipd'   => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referback_inprov'      => ['nullable', 'integer', 'min:0'],
            'records.*.visit_referback_outprov'     => ['nullable', 'integer', 'min:0'],
        ]);

        $hospcode = $hospital->hospcode ?? $hospital->hcode;
        $rows = $validated['records'];

        // เตรียมวันที่ทั้งหมดจาก payload
        $dates = collect($rows)->pluck('vstdate')->unique()->values();

        // เช็ควันที่ที่มีอยู่แล้วใน DB (ของ hospcode นี้)
        $existing = Refer::query()
            ->where('hospcode', $hospcode)
            ->whereIn('vstdate', $dates)
            ->pluck('vstdate')
            ->all();

        $existingSet = [];
        foreach ($existing as $d) {
            if (is_string($d) || is_int($d)) {
                $existingSet[(string)$d] = true;
            }
        }

        // กัน payload ซ้ำวันที่เดียวกัน: อันหลังทับอันแรก
        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['vstdate']] = $r;
        }

        $now = now();
        $toUpsert = [];
        foreach ($byDate as $vstdate => $row) {
            $toUpsert[] = [
                'hospcode' => $hospcode,
                'vstdate'  => $vstdate,
                'visit_referout_inprov'       => $row['visit_referout_inprov'] ?? 0,
                'visit_referout_outprov'      => $row['visit_referout_outprov'] ?? 0,
                'visit_referout_inprov_ipd'   => $row['visit_referout_inprov_ipd'] ?? 0,
                'visit_referout_outprov_ipd'  => $row['visit_referout_outprov_ipd'] ?? 0,
                'visit_referin_inprov'        => $row['visit_referin_inprov'] ?? 0,
                'visit_referin_outprov'       => $row['visit_referin_outprov'] ?? 0,
                'visit_referin_inprov_ipd'    => $row['visit_referin_inprov_ipd'] ?? 0,
                'visit_referin_outprov_ipd'   => $row['visit_referin_outprov_ipd'] ?? 0,
                'visit_referback_inprov'      => $row['visit_referback_inprov'] ?? 0,
                'visit_referback_outprov'     => $row['visit_referback_outprov'] ?? 0,
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ];
        }

        $payloadDates  = array_keys($byDate);
        $existingDates = array_keys($existingSet);
        $created = count(array_diff($payloadDates, $existingDates));
        $updated = count(array_intersect($payloadDates, $existingDates));

        if (!empty($toUpsert)) {
            DB::beginTransaction();
            try {
                DB::table('refer')->upsert(
                    $toUpsert,
                    ['hospcode', 'vstdate'],
                    [
                        'visit_referout_inprov',
                        'visit_referout_outprov',
                        'visit_referout_inprov_ipd',
                        'visit_referout_outprov_ipd',
                        'visit_referin_inprov',
                        'visit_referin_outprov',
                        'visit_referin_inprov_ipd',
                        'visit_referin_outprov_ipd',
                        'visit_referback_inprov',
                        'visit_referback_outprov',
                        'updated_at',
                    ]
                );
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
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

        return response()->json([
            'hospcode' => $hospcode,
            'created'  => $created,
            'updated'  => $updated,
            'errors'   => [],
        ], 200);
    }

    public function get_refer(Request $request)
    {
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $hospcode = $hospital->hospcode ?? $hospital->hcode;
        $superHospcodes = ['00025'];

        $start_date = $request->query('start_date') ?? Carbon::now()->subDays(10)->format('Y-m-d');
        $end_date = $request->query('end_date') ?? Carbon::now()->format('Y-m-d');
        $limit = $request->query('limit', 200);

        $query = DB::table('refer');

        if (!in_array($hospcode, $superHospcodes)) {
            $query->where('hospcode', $hospcode);
        }

        if ($start_date && $end_date) {
            $query->whereBetween('vstdate', [$start_date, $end_date]);
        }

        $data = $query->orderBy('vstdate', 'desc')->limit($limit)->get();

        return response()->json([
            'ok' => true,
            'hospcode' => $hospcode,
            'super' => in_array($hospcode, $superHospcodes),
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
