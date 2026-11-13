<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohIpdHospitalSummary extends Model
{
    use HasFactory;

    protected $table = 'opoh_ipd_hospital_summaries';

    protected $fillable = [
        'hospcode',
        'dchdate',
        'an_total',
        'admdate_total',
        'adjrw_total',
        'operation_total',
        'an_ucs_incup',
        'an_ucs_outprov',
        'an_ofc',
        'an_lgo',
        'an_sss',
        'an_other',
        'admdate_ucs_incup',
        'admdate_ucs_outprov',
        'admdate_ofc',
        'admdate_lgo',
        'admdate_sss',
        'admdate_other',
        'adjrw_ucs_incup',
        'adjrw_ucs_outprov',
        'adjrw_ofc',
        'adjrw_lgo',
        'adjrw_sss',
        'adjrw_other',
    ];

    protected $casts = [
        'dchdate'         => 'date',
        'an_total'        => 'integer',
        'admdate_total'   => 'integer',
        'adjrw_total'     => 'decimal:4',
        'operation_total' => 'integer',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }
}

