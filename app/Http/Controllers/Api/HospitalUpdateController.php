<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HospitalUpdateController extends Controller
{
    /**
     * PUT /api/hospital/update
     * อัปเดต bed_qty และ bed_use ของโรงพยาบาล (รองรับหลาย record)
     */
    public function update(Request $request)
    {
        // ตรวจสอบสิทธิ์
        $hospital = Auth::user();
        if (!$hospital || !$hospital->tokenCan('ingest')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // รับ array ของ record
        $records = $request->input('records', []);
        if (!is_array($records) || empty($records)) {
            return response()->json([
                'ok' => false,
                'message' => 'No records provided.'
            ], 422);
        }

        $results = [];

        foreach ($records as $index => $r) {
            // Validate แต่ละ record
            try {
                $validated = validator($r, [
                    'hospcode' => ['sometimes','string'],
                    'bed_qty'  => ['required','integer','min:0'],
                    'bed_use'  => ['required','integer','min:0'],
                ])->validate();
            } catch (\Illuminate\Validation\ValidationException $e) {
                $results[] = [
                    'index' => $index,
                    'ok' => false,
                    'errors' => $e->errors(),
                ];
                continue;
            }

            $hospcode = $validated['hospcode'] ?? $hospital->hospcode;

            try {
                $affected = DB::table('hospital_config')
                    ->where('hospcode', $hospcode)
                    ->update([
                        'bed_qty' => $validated['bed_qty'],
                        'bed_use' => $validated['bed_use'],
                        'updated_at' => now(),
                    ]);

                if ($affected === 0) {
                    $results[] = [
                        'hospcode' => $hospcode,
                        'ok' => false,
                        'message' => "No record found for hospcode {$hospcode}. Update failed.",
                    ];
                } else {
                    $results[] = [
                        'hospcode' => $hospcode,
                        'ok' => true,
                        'updated' => $affected,
                    ];
                }
            } catch (\Throwable $e) {
                $results[] = [
                    'hospcode' => $hospcode,
                    'ok' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Return ผลลัพธ์รวม
        return response()->json([
            'ok' => collect($results)->every(fn($r) => $r['ok'] === true),
            'results' => $results,
        ]);
    }
}
