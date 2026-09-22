<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Operation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationController extends Controller
{
    public function operation(Request $request)
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
            'records.*.visit_operation' => ['required', 'integer', 'min:0'],
        ]);

        $hospcode = $hospital->hospcode ?? $hospital->hcode;
        $rows = $validated['records'];

        $dates = collect($rows)->pluck('vstdate')->unique()->values();

        $existing = Operation::query()
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

        $byDate = [];
        foreach ($rows as $r) {
            $byDate[$r['vstdate']] = $r;
        }

        $now = now();
        $toUpsert = [];
        foreach ($byDate as $vstdate => $row) {
            $toUpsert[] = [
                'hospcode'        => $hospcode,
                'vstdate'         => $vstdate,
                'visit_operation' => $row['visit_operation'] ?? 0,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        $payloadDates  = array_keys($byDate);
        $existingDates = array_keys($existingSet);
        $created = count(array_diff($payloadDates, $existingDates));
        $updated = count(array_intersect($payloadDates, $existingDates));

        if (!empty($toUpsert)) {
            DB::beginTransaction();
            try {
                DB::table('operation')->upsert(
                    $toUpsert,
                    ['hospcode', 'vstdate'],
                    [
                        'visit_operation',
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

    public function get_operation(Request $request)
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

        $query = DB::table('operation');

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
