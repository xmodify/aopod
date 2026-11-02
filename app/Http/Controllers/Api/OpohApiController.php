<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpohDoctor;
use App\Models\OpohIpdService;
use App\Models\OpohOpdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpohApiController extends Controller
{
    /**
     * GET /api/agent/opoh-doctors
     * Return active doctors list, CIDs, licenses, and names for Agent querying HOSxP
     */
    public function getDoctors(Request $request)
    {
        $doctors = OpohDoctor::active()->get();

        $cids = $doctors->pluck('cid')->filter()->unique()->values()->toArray();
        $licenses = $doctors->pluck('licenseno')->filter()->unique()->values()->toArray();

        // Extract clean first names without title for fallback LIKE matching
        $names = $doctors->map(function ($doc) {
            return trim($doc->fname);
        })->filter()->unique()->values()->toArray();

        return response()->json([
            'status'   => 'success',
            'count'    => $doctors->count(),
            'cids'     => $cids,
            'licenses' => $licenses,
            'names'    => $names,
            'data'     => $doctors->map(function ($d) {
                return [
                    'id'             => $d->id,
                    'doctor_name'    => $d->doctor_name,
                    'fname'          => $d->fname,
                    'lname'          => $d->lname,
                    'cid'            => $d->cid,
                    'licenseno'      => $d->licenseno,
                    'spclty_code'    => $d->spclty_code,
                    'specialty_name' => $d->specialty_name,
                ];
            }),
        ]);
    }

    /**
     * POST /api/opoh/opd
     * Receive aggregated OPD summary records from Agent
     */
    public function ingestOpd(Request $request)
    {
        $hospital = Auth::user();
        $hospcode = $hospital->hospcode ?? $request->input('hospcode');

        if (empty($hospcode)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing hospital code',
            ], 400);
        }

        $validated = $request->validate([
            'records'                   => ['required', 'array', 'min:1'],
            'records.*.vstdate'         => ['required', 'date_format:Y-m-d'],
            'records.*.spclty_code'     => ['required', 'string'],
            'records.*.doctor_id'       => ['nullable', 'integer'],
            'records.*.licenseno'       => ['nullable', 'string'],
            'records.*.specialty_name'  => ['nullable', 'string'],
            'records.*.hn_total'        => ['nullable', 'integer', 'min:0'],
            'records.*.visit_total'     => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ucs'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ofc'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_lgo'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_sss'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_other'     => ['nullable', 'integer', 'min:0'],
            'records.*.inc_total'       => ['nullable', 'numeric', 'min:0'],
            'records.*.inc_drug_total'  => ['nullable', 'numeric', 'min:0'],
            'records.*.inc_lab_total'   => ['nullable', 'numeric', 'min:0'],
            'records.*.refer_to_origin' => ['nullable', 'integer', 'min:0'],
            'records.*.admit_ipd'       => ['nullable', 'integer', 'min:0'],
        ]);

        $inserted = 0;
        DB::beginTransaction();
        try {
            foreach ($validated['records'] as $record) {
                $docId = $record['doctor_id'] ?? null;
                $license = $record['licenseno'] ?? null;
                if (!$docId && $license) {
                    $docId = DB::table('opoh_doctors')->where('licenseno', $license)->value('id');
                }

                $matchCriteria = [
                    'hospcode'    => $hospcode,
                    'vstdate'     => $record['vstdate'],
                    'spclty_code' => $record['spclty_code'],
                ];
                if ($docId) {
                    $matchCriteria['doctor_id'] = $docId;
                }

                OpohOpdService::updateOrCreate(
                    $matchCriteria,
                    [
                        'doctor_id'       => $docId,
                        'licenseno'       => $license,
                        'specialty_name'  => $record['specialty_name'] ?? 'ไม่ระบุสาขา',
                        'hn_total'        => (int)($record['hn_total'] ?? 0),
                        'visit_total'     => (int)($record['visit_total'] ?? 0),
                        'visit_ucs'       => (int)($record['visit_ucs'] ?? 0),
                        'visit_ofc'       => (int)($record['visit_ofc'] ?? 0),
                        'visit_lgo'       => (int)($record['visit_lgo'] ?? 0),
                        'visit_sss'       => (int)($record['visit_sss'] ?? 0),
                        'visit_other'     => (int)($record['visit_other'] ?? 0),
                        'inc_total'       => (float)($record['inc_total'] ?? 0.00),
                        'inc_drug_total'  => (float)($record['inc_drug_total'] ?? 0.00),
                        'inc_lab_total'   => (float)($record['inc_lab_total'] ?? 0.00),
                        'refer_to_origin' => (int)($record['refer_to_origin'] ?? 0),
                        'admit_ipd'       => (int)($record['admit_ipd'] ?? 0),
                    ]
                );
                $inserted++;
            }
            DB::commit();

            return response()->json([
                'status'   => 'success',
                'hospcode' => $hospcode,
                'count'    => $inserted,
                'message'  => "Synchronized {$inserted} OPOH OPD records successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[OPOH Ingest Error] " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save OPOH data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/opoh/ipd
     * Receive aggregated IPD summary records from Agent
     */
    public function ingestIpd(Request $request)
    {
        $hospital = Auth::user();
        $hospcode = $hospital->hospcode ?? $request->input('hospcode');

        if (empty($hospcode)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Missing hospital code',
            ], 400);
        }

        $validated = $request->validate([
            'records'                   => ['required', 'array', 'min:1'],
            'records.*.dchdate'         => ['required', 'date_format:Y-m-d'],
            'records.*.spclty_code'     => ['required', 'string'],
            'records.*.doctor_id'       => ['nullable', 'integer'],
            'records.*.licenseno'       => ['nullable', 'string'],
            'records.*.specialty_name'  => ['nullable', 'string'],
            'records.*.an_total'        => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_total'   => ['nullable', 'integer', 'min:0'],
            'records.*.adjrw_total'     => ['nullable', 'numeric', 'min:0'],
            'records.*.operation_total' => ['nullable', 'integer', 'min:0'],
            'records.*.inc_total'       => ['nullable', 'numeric', 'min:0'],
        ]);

        $inserted = 0;
        DB::beginTransaction();
        try {
            foreach ($validated['records'] as $record) {
                $docId = $record['doctor_id'] ?? null;
                $license = $record['licenseno'] ?? null;
                if (!$docId && $license) {
                    $docId = DB::table('opoh_doctors')->where('licenseno', $license)->value('id');
                }

                $matchCriteria = [
                    'hospcode'    => $hospcode,
                    'dchdate'     => $record['dchdate'],
                    'spclty_code' => $record['spclty_code'],
                ];
                if ($docId) {
                    $matchCriteria['doctor_id'] = $docId;
                }

                OpohIpdService::updateOrCreate(
                    $matchCriteria,
                    [
                        'doctor_id'       => $docId,
                        'licenseno'       => $license,
                        'specialty_name'  => $record['specialty_name'] ?? 'ไม่ระบุสาขา',
                        'an_total'        => (int)($record['an_total'] ?? 0),
                        'admdate_total'   => (int)($record['admdate_total'] ?? 0),
                        'adjrw_total'     => (float)($record['adjrw_total'] ?? 0.0000),
                        'operation_total' => (int)($record['operation_total'] ?? 0),
                        'inc_total'       => (float)($record['inc_total'] ?? 0.00),
                    ]
                );
                $inserted++;
            }
            DB::commit();

            return response()->json([
                'status'   => 'success',
                'hospcode' => $hospcode,
                'count'    => $inserted,
                'message'  => "Synchronized {$inserted} OPOH IPD records successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[OPOH IPD Ingest Error] " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save OPOH IPD data: ' . $e->getMessage(),
            ], 500);
        }
    }
}

