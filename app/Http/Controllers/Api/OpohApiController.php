<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpohDoctor;
use App\Models\OpohIpdService;
use App\Models\OpohIpdHospitalSummary;
use App\Models\OpohIpdIncomeSummary;
use App\Models\OpohOpdService;
use App\Models\OpohOperationService;
use App\Models\OpohOpdDiagnosis;
use App\Models\OpohIpdDiagnosis;
use App\Models\OpohIncomeCategory;
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
                    'cid'             => $d->cid,
                    'licenseno'       => $d->licenseno,
                    'spclty_code'     => $d->spclty_code,
                    'specialty_name'  => $d->specialty_name,
                    'origin_hospcode' => $d->origin_hospcode,
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
            'records'                     => ['required', 'array', 'min:1'],
            'records.*.vstdate'           => ['required', 'date_format:Y-m-d'],
            'records.*.spclty_code'       => ['required', 'string'],
            'records.*.doctor_id'         => ['nullable', 'integer'],
            'records.*.licenseno'         => ['nullable', 'string'],
            'records.*.specialty_name'    => ['nullable', 'string'],
            'records.*.hn_total'          => ['nullable', 'integer', 'min:0'],
            'records.*.visit_total'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ucs'         => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ofc'         => ['nullable', 'integer', 'min:0'],
            'records.*.visit_lgo'         => ['nullable', 'integer', 'min:0'],
            'records.*.visit_sss'         => ['nullable', 'integer', 'min:0'],
            'records.*.visit_other'       => ['nullable', 'integer', 'min:0'],
            'records.*.visit_telemed'     => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ucs_incup'   => ['nullable', 'integer', 'min:0'],
            'records.*.visit_ucs_outprov' => ['nullable', 'integer', 'min:0'],
            'records.*.refer_to_origin'   => ['nullable', 'integer', 'min:0'],
            'records.*.admit_ipd'         => ['nullable', 'integer', 'min:0'],
            'records.*.diagnoses'         => ['nullable', 'array'],
            'records.*.income_categories' => ['nullable', 'array'],
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

                // If visit_total is 0 (e.g. all visits on this day were dialysis and excluded), delete old ghost record
                if ((int)($record['visit_total'] ?? 0) === 0) {
                    OpohOpdService::where($matchCriteria)->delete();
                    OpohOpdDiagnosis::where('hospcode', $hospcode)
                        ->where('vstdate', $record['vstdate'])
                        ->where('spclty_code', $record['spclty_code'])
                        ->when($docId, fn($q) => $q->where('doctor_id', $docId))
                        ->delete();
                    $inserted++;
                    continue;
                }

                OpohOpdService::updateOrCreate(
                    $matchCriteria,
                    [
                        'doctor_id'         => $docId,
                        'licenseno'         => $license,
                        'specialty_name'    => $record['specialty_name'] ?? 'ไม่ระบุสาขา',
                        'hn_total'          => (int)($record['hn_total'] ?? 0),
                        'visit_total'       => (int)($record['visit_total'] ?? 0),
                        'visit_ucs'         => (int)($record['visit_ucs'] ?? 0),
                        'visit_ofc'         => (int)($record['visit_ofc'] ?? 0),
                        'visit_lgo'         => (int)($record['visit_lgo'] ?? 0),
                        'visit_sss'         => (int)($record['visit_sss'] ?? 0),
                        'visit_other'       => (int)($record['visit_other'] ?? 0),
                        'visit_telemed'     => (int)($record['visit_telemed'] ?? 0),
                        'visit_ucs_incup'   => (int)($record['visit_ucs_incup'] ?? 0),
                        'visit_ucs_outprov' => (int)($record['visit_ucs_outprov'] ?? 0),
                        'refer_to_origin'   => (int)($record['refer_to_origin'] ?? 0),
                        'admit_ipd'         => (int)($record['admit_ipd'] ?? 0),
                    ]
                );
                $inserted++;

                // Ingest Top OPD Diagnoses if attached
                if (!empty($record['diagnoses']) && is_array($record['diagnoses'])) {
                    foreach ($record['diagnoses'] as $diag) {
                        $icd10 = trim($diag['icd10'] ?? '');
                        if ($icd10 === '') continue;

                        OpohOpdDiagnosis::updateOrCreate(
                            [
                                'hospcode'    => $hospcode,
                                'vstdate'     => $record['vstdate'],
                                'spclty_code' => $record['spclty_code'],
                                'doctor_id'   => $docId,
                                'icd10'       => $icd10,
                            ],
                            [
                                'diag_name'   => $diag['diag_name'] ?? $icd10,
                                'case_total'  => (int)($diag['case_total'] ?? 1),
                            ]
                        );
                    }
                }

                // Ingest Income Categories if attached directly to OPD record
                if (!empty($record['income_categories']) && is_array($record['income_categories'])) {
                    foreach ($record['income_categories'] as $cat) {
                        $drgId = (int)($cat['drg_chrgitem_id'] ?? 0);
                        if ($drgId <= 0) continue;

                        OpohIncomeCategory::updateOrCreate(
                            [
                                'hospcode'        => $hospcode,
                                'service_type'    => 'OPD',
                                'vstdate'         => $record['vstdate'],
                                'doctor_id'       => $docId,
                                'drg_chrgitem_id' => $drgId,
                            ],
                            [
                                'spclty_code'       => $record['spclty_code'],
                                'licenseno'         => $license,
                                'drg_chrgitem_name' => $cat['drg_chrgitem_name'] ?? 'หมวดที่ ' . $drgId,
                                'inc_ucs_incup'     => (float)($cat['inc_ucs_incup'] ?? 0.00),
                                'inc_ucs_outprov'   => (float)($cat['inc_ucs_outprov'] ?? 0.00),
                                'inc_ofc'           => (float)($cat['inc_ofc'] ?? 0.00),
                                'inc_lgo'           => (float)($cat['inc_lgo'] ?? 0.00),
                                'inc_sss'           => (float)($cat['inc_sss'] ?? 0.00),
                                'inc_other'         => (float)($cat['inc_other'] ?? 0.00),
                                'inc_total'         => (float)($cat['inc_total'] ?? 0.00),
                            ]
                        );
                    }
                }
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
            'records'                     => ['nullable', 'array'],
            'records.*.dchdate'           => ['required_with:records', 'date_format:Y-m-d'],
            'records.*.spclty_code'       => ['required_with:records', 'string'],
            'records.*.doctor_id'         => ['nullable', 'integer'],
            'records.*.licenseno'         => ['nullable', 'string'],
            'records.*.specialty_name'    => ['nullable', 'string'],
            'records.*.an_total'          => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_total'     => ['nullable', 'integer', 'min:0'],
            'records.*.adjrw_total'       => ['nullable', 'numeric', 'min:0'],
            'records.*.operation_total'   => ['nullable', 'integer', 'min:0'],

            'records.*.an_ucs_incup'      => ['nullable', 'integer', 'min:0'],
            'records.*.an_ucs_outprov'    => ['nullable', 'integer', 'min:0'],
            'records.*.an_ofc'            => ['nullable', 'integer', 'min:0'],
            'records.*.an_lgo'            => ['nullable', 'integer', 'min:0'],
            'records.*.an_sss'            => ['nullable', 'integer', 'min:0'],
            'records.*.an_other'          => ['nullable', 'integer', 'min:0'],

            'records.*.admdate_ucs_incup' => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_ucs_outprov'=> ['nullable', 'integer', 'min:0'],
            'records.*.admdate_ofc'       => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_lgo'       => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_sss'       => ['nullable', 'integer', 'min:0'],
            'records.*.admdate_other'     => ['nullable', 'integer', 'min:0'],

            'records.*.adjrw_ucs_incup'   => ['nullable', 'numeric', 'min:0'],
            'records.*.adjrw_ucs_outprov' => ['nullable', 'numeric', 'min:0'],
            'records.*.adjrw_ofc'         => ['nullable', 'numeric', 'min:0'],
            'records.*.adjrw_lgo'         => ['nullable', 'numeric', 'min:0'],
            'records.*.adjrw_sss'         => ['nullable', 'numeric', 'min:0'],
            'records.*.adjrw_other'       => ['nullable', 'numeric', 'min:0'],

            'records.*.diagnoses'         => ['nullable', 'array'],
            'records.*.income_categories' => ['nullable', 'array'],

            // สรุปรวมระดับโรงพยาบาลแบบไม่นับซ้ำราย AN
            'hospital_summaries'                     => ['nullable', 'array'],
            'hospital_summaries.*.dchdate'           => ['required_with:hospital_summaries', 'date_format:Y-m-d'],
            'hospital_summaries.*.an_total'          => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_total'     => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.adjrw_total'       => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.operation_total'   => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_ucs_incup'      => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_ucs_outprov'    => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_ofc'            => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_lgo'            => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_sss'            => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.an_other'          => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_ucs_incup' => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_ucs_outprov'=> ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_ofc'       => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_lgo'       => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_sss'       => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.admdate_other'     => ['nullable', 'integer', 'min:0'],
            'hospital_summaries.*.adjrw_ucs_incup'   => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.adjrw_ucs_outprov' => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.adjrw_ofc'         => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.adjrw_lgo'         => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.adjrw_sss'         => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.adjrw_other'       => ['nullable', 'numeric', 'min:0'],
            'hospital_summaries.*.income_categories' => ['nullable', 'array'],
        ]);

        $records = $validated['records'] ?? [];
        $hospitalSummaries = $validated['hospital_summaries'] ?? [];
        if (empty($records) && empty($hospitalSummaries)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The records field is required.',
            ], 422);
        }

        $inserted = 0;
        DB::beginTransaction();
        try {
            foreach ($records as $record) {
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
                        'doctor_id'           => $docId,
                        'licenseno'           => $license,
                        'specialty_name'      => $record['specialty_name'] ?? 'ไม่ระบุสาขา',
                        'an_total'            => (int)($record['an_total'] ?? 0),
                        'admdate_total'       => (int)($record['admdate_total'] ?? 0),
                        'adjrw_total'         => (float)($record['adjrw_total'] ?? 0.0000),
                        'operation_total'     => (int)($record['operation_total'] ?? 0),

                        'an_ucs_incup'        => (int)($record['an_ucs_incup'] ?? 0),
                        'an_ucs_outprov'      => (int)($record['an_ucs_outprov'] ?? 0),
                        'an_ofc'              => (int)($record['an_ofc'] ?? 0),
                        'an_lgo'              => (int)($record['an_lgo'] ?? 0),
                        'an_sss'              => (int)($record['an_sss'] ?? 0),
                        'an_other'            => (int)($record['an_other'] ?? 0),

                        'admdate_ucs_incup'   => (int)($record['admdate_ucs_incup'] ?? 0),
                        'admdate_ucs_outprov' => (int)($record['admdate_ucs_outprov'] ?? 0),
                        'admdate_ofc'         => (int)($record['admdate_ofc'] ?? 0),
                        'admdate_lgo'         => (int)($record['admdate_lgo'] ?? 0),
                        'admdate_sss'         => (int)($record['admdate_sss'] ?? 0),
                        'admdate_other'       => (int)($record['admdate_other'] ?? 0),

                        'adjrw_ucs_incup'     => (float)($record['adjrw_ucs_incup'] ?? 0.0000),
                        'adjrw_ucs_outprov'   => (float)($record['adjrw_ucs_outprov'] ?? 0.0000),
                        'adjrw_ofc'           => (float)($record['adjrw_ofc'] ?? 0.0000),
                        'adjrw_lgo'           => (float)($record['adjrw_lgo'] ?? 0.0000),
                        'adjrw_sss'           => (float)($record['adjrw_sss'] ?? 0.0000),
                        'adjrw_other'         => (float)($record['adjrw_other'] ?? 0.0000),
                    ]
                );
                $inserted++;

                // Ingest Top IPD Diagnoses if attached
                if (!empty($record['diagnoses']) && is_array($record['diagnoses'])) {
                    foreach ($record['diagnoses'] as $diag) {
                        $icd10 = trim($diag['icd10'] ?? '');
                        if ($icd10 === '') continue;

                        OpohIpdDiagnosis::updateOrCreate(
                            [
                                'hospcode'    => $hospcode,
                                'dchdate'     => $record['dchdate'],
                                'spclty_code' => $record['spclty_code'],
                                'doctor_id'   => $docId,
                                'icd10'       => $icd10,
                            ],
                            [
                                'diag_name'   => $diag['diag_name'] ?? $icd10,
                                'case_total'  => (int)($diag['case_total'] ?? 1),
                            ]
                        );
                    }
                }

                // Ingest IPD Income Categories if attached directly
                if (!empty($record['income_categories']) && is_array($record['income_categories'])) {
                    foreach ($record['income_categories'] as $cat) {
                        $drgId = (int)($cat['drg_chrgitem_id'] ?? 0);
                        if ($drgId <= 0) continue;

                        OpohIncomeCategory::updateOrCreate(
                            [
                                'hospcode'        => $hospcode,
                                'service_type'    => 'IPD',
                                'vstdate'         => $record['dchdate'],
                                'doctor_id'       => $docId,
                                'drg_chrgitem_id' => $drgId,
                            ],
                            [
                                'spclty_code'       => $record['spclty_code'],
                                'licenseno'         => $license,
                                'drg_chrgitem_name' => $cat['drg_chrgitem_name'] ?? 'หมวดที่ ' . $drgId,
                                'inc_ucs_incup'     => (float)($cat['inc_ucs_incup'] ?? 0.00),
                                'inc_ucs_outprov'   => (float)($cat['inc_ucs_outprov'] ?? 0.00),
                                'inc_ofc'           => (float)($cat['inc_ofc'] ?? 0.00),
                                'inc_lgo'           => (float)($cat['inc_lgo'] ?? 0.00),
                                'inc_sss'           => (float)($cat['inc_sss'] ?? 0.00),
                                'inc_other'         => (float)($cat['inc_other'] ?? 0.00),
                                'inc_total'         => (float)($cat['inc_total'] ?? 0.00),
                            ]
                        );
                    }
                }
            }

            foreach ($hospitalSummaries as $hSum) {
                $dchdate = $hSum['dchdate'];
                if ((int)($hSum['an_total'] ?? 0) === 0) {
                    OpohIpdHospitalSummary::where('hospcode', $hospcode)->where('dchdate', $dchdate)->delete();
                    OpohIpdIncomeSummary::where('hospcode', $hospcode)->where('dchdate', $dchdate)->delete();
                    continue;
                }

                OpohIpdHospitalSummary::updateOrCreate(
                    ['hospcode' => $hospcode, 'dchdate' => $dchdate],
                    [
                        'an_total'            => (int)($hSum['an_total'] ?? 0),
                        'admdate_total'       => (int)($hSum['admdate_total'] ?? 0),
                        'adjrw_total'         => (float)($hSum['adjrw_total'] ?? 0.0000),
                        'operation_total'     => (int)($hSum['operation_total'] ?? 0),
                        'an_ucs_incup'        => (int)($hSum['an_ucs_incup'] ?? 0),
                        'an_ucs_outprov'      => (int)($hSum['an_ucs_outprov'] ?? 0),
                        'an_ofc'              => (int)($hSum['an_ofc'] ?? 0),
                        'an_lgo'              => (int)($hSum['an_lgo'] ?? 0),
                        'an_sss'              => (int)($hSum['an_sss'] ?? 0),
                        'an_other'            => (int)($hSum['an_other'] ?? 0),
                        'admdate_ucs_incup'   => (int)($hSum['admdate_ucs_incup'] ?? 0),
                        'admdate_ucs_outprov' => (int)($hSum['admdate_ucs_outprov'] ?? 0),
                        'admdate_ofc'         => (int)($hSum['admdate_ofc'] ?? 0),
                        'admdate_lgo'         => (int)($hSum['admdate_lgo'] ?? 0),
                        'admdate_sss'         => (int)($hSum['admdate_sss'] ?? 0),
                        'admdate_other'       => (int)($hSum['admdate_other'] ?? 0),
                        'adjrw_ucs_incup'     => (float)($hSum['adjrw_ucs_incup'] ?? 0.0000),
                        'adjrw_ucs_outprov'   => (float)($hSum['adjrw_ucs_outprov'] ?? 0.0000),
                        'adjrw_ofc'           => (float)($hSum['adjrw_ofc'] ?? 0.0000),
                        'adjrw_lgo'           => (float)($hSum['adjrw_lgo'] ?? 0.0000),
                        'adjrw_sss'           => (float)($hSum['adjrw_sss'] ?? 0.0000),
                        'adjrw_other'         => (float)($hSum['adjrw_other'] ?? 0.0000),
                    ]
                );

                if (!empty($hSum['income_categories']) && is_array($hSum['income_categories'])) {
                    $seenIds = [];
                    foreach ($hSum['income_categories'] as $cat) {
                        $drgId = (int)($cat['drg_chrgitem_id'] ?? 0);
                        if ($drgId <= 0) continue;
                        $seenIds[] = $drgId;

                        OpohIpdIncomeSummary::updateOrCreate(
                            [
                                'hospcode'        => $hospcode,
                                'dchdate'         => $dchdate,
                                'drg_chrgitem_id' => $drgId,
                            ],
                            [
                                'drg_chrgitem_name' => $cat['drg_chrgitem_name'] ?? 'หมวดที่ ' . $drgId,
                                'inc_ucs_incup'     => (float)($cat['inc_ucs_incup'] ?? 0.00),
                                'inc_ucs_outprov'   => (float)($cat['inc_ucs_outprov'] ?? 0.00),
                                'inc_ofc'           => (float)($cat['inc_ofc'] ?? 0.00),
                                'inc_lgo'           => (float)($cat['inc_lgo'] ?? 0.00),
                                'inc_sss'           => (float)($cat['inc_sss'] ?? 0.00),
                                'inc_other'         => (float)($cat['inc_other'] ?? 0.00),
                                'inc_total'         => (float)($cat['inc_total'] ?? 0.00),
                            ]
                        );
                    }
                    if (!empty($seenIds)) {
                        OpohIpdIncomeSummary::where('hospcode', $hospcode)
                            ->where('dchdate', $dchdate)
                            ->whereNotIn('drg_chrgitem_id', $seenIds)
                            ->delete();
                    }
                }
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

    /**
     * POST /api/opoh/operation
     * Receive aggregated operation & procedure summary records from Agent (Tab 3)
     */
    public function ingestOperation(Request $request)
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
            'records'                     => ['required', 'array', 'min:1'],
            'records.*.operation_date'    => ['required', 'date_format:Y-m-d'],
            'records.*.spclty_code'       => ['required', 'string'],
            'records.*.specialty_name'    => ['nullable', 'string'],
            'records.*.doctor_id'         => ['nullable', 'integer'],
            'records.*.licenseno'         => ['nullable', 'string'],
            'records.*.icd9'              => ['nullable', 'string'],
            'records.*.operation_name'    => ['required', 'string'],
            'records.*.op_type'           => ['nullable', 'string'],
            'records.*.case_total'        => ['nullable', 'integer', 'min:1'],
            'records.*.adjrw_total'       => ['nullable', 'numeric', 'min:0'],

            'records.*.case_ucs_incup'    => ['nullable', 'integer', 'min:0'],
            'records.*.case_ucs_outprov'  => ['nullable', 'integer', 'min:0'],
            'records.*.case_ofc'          => ['nullable', 'integer', 'min:0'],
            'records.*.case_lgo'          => ['nullable', 'integer', 'min:0'],
            'records.*.case_sss'          => ['nullable', 'integer', 'min:0'],
            'records.*.case_other'        => ['nullable', 'integer', 'min:0'],
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
                    'hospcode'       => $hospcode,
                    'operation_date' => $record['operation_date'],
                    'spclty_code'    => $record['spclty_code'],
                    'doctor_id'      => $docId,
                    'icd9'           => $record['icd9'] ?? '',
                    'op_type'        => $record['op_type'] ?? 'IPD',
                ];

                OpohOperationService::updateOrCreate(
                    $matchCriteria,
                    [
                        'specialty_name'  => $record['specialty_name'] ?? 'ไม่ระบุสาขา',
                        'licenseno'       => $license,
                        'operation_name'  => $record['operation_name'],
                        'case_total'      => (int)($record['case_total'] ?? 1),
                        'adjrw_total'     => (float)($record['adjrw_total'] ?? 0.0000),

                        'case_ucs_incup'  => (int)($record['case_ucs_incup'] ?? 0),
                        'case_ucs_outprov'=> (int)($record['case_ucs_outprov'] ?? 0),
                        'case_ofc'        => (int)($record['case_ofc'] ?? 0),
                        'case_lgo'        => (int)($record['case_lgo'] ?? 0),
                        'case_sss'        => (int)($record['case_sss'] ?? 0),
                        'case_other'      => (int)($record['case_other'] ?? 0),
                    ]
                );
                $inserted++;
            }
            DB::commit();

            return response()->json([
                'status'   => 'success',
                'hospcode' => $hospcode,
                'count'    => $inserted,
                'message'  => "Synchronized {$inserted} OPOH Operation records successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[OPOH Operation Ingest Error] " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save OPOH Operation data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/opoh/income-categories
     * Dedicated endpoint to receive aggregated income category records from Agent
     */
    public function ingestIncomeCategories(Request $request)
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
            'records'                      => ['required', 'array', 'min:1'],
            'records.*.service_type'       => ['nullable', 'string', 'in:OPD,IPD'],
            'records.*.vstdate'            => ['required', 'date_format:Y-m-d'],
            'records.*.spclty_code'        => ['nullable', 'string'],
            'records.*.doctor_id'          => ['nullable', 'integer'],
            'records.*.licenseno'          => ['nullable', 'string'],
            'records.*.drg_chrgitem_id'    => ['required', 'integer'],
            'records.*.drg_chrgitem_name'  => ['nullable', 'string'],
            'records.*.inc_ucs_incup'      => ['nullable', 'numeric'],
            'records.*.inc_ucs_outprov'    => ['nullable', 'numeric'],
            'records.*.inc_ofc'            => ['nullable', 'numeric'],
            'records.*.inc_lgo'            => ['nullable', 'numeric'],
            'records.*.inc_sss'            => ['nullable', 'numeric'],
            'records.*.inc_other'          => ['nullable', 'numeric'],
            'records.*.inc_total'          => ['nullable', 'numeric'],
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

                $drgId = (int)$record['drg_chrgitem_id'];
                $serviceType = strtoupper($record['service_type'] ?? 'OPD');

                OpohIncomeCategory::updateOrCreate(
                    [
                        'hospcode'        => $hospcode,
                        'service_type'    => $serviceType,
                        'vstdate'         => $record['vstdate'],
                        'doctor_id'       => $docId,
                        'drg_chrgitem_id' => $drgId,
                    ],
                    [
                        'spclty_code'       => $record['spclty_code'] ?? '01',
                        'licenseno'         => $license,
                        'drg_chrgitem_name' => $record['drg_chrgitem_name'] ?? 'หมวดที่ ' . $drgId,
                        'inc_ucs_incup'     => (float)($record['inc_ucs_incup'] ?? 0.00),
                        'inc_ucs_outprov'   => (float)($record['inc_ucs_outprov'] ?? 0.00),
                        'inc_ofc'           => (float)($record['inc_ofc'] ?? 0.00),
                        'inc_lgo'           => (float)($record['inc_lgo'] ?? 0.00),
                        'inc_sss'           => (float)($record['inc_sss'] ?? 0.00),
                        'inc_other'         => (float)($record['inc_other'] ?? 0.00),
                        'inc_total'         => (float)($record['inc_total'] ?? 0.00),
                    ]
                );
                $inserted++;
            }
            DB::commit();

            return response()->json([
                'status'   => 'success',
                'hospcode' => $hospcode,
                'count'    => $inserted,
                'message'  => "Synchronized {$inserted} OPOH Income Category records successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[OPOH Income Category Ingest Error] " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to save OPOH Income Category data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
