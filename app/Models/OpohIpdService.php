<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohIpdService extends Model
{
    use HasFactory;

    protected $table = 'opoh_ipd_service';

    protected $fillable = [
        'hospcode',
        'dchdate',
        'spclty_code',
        'doctor_id',
        'licenseno',
        'specialty_name',
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
        'dchdate'             => 'date',
        'an_total'            => 'integer',
        'admdate_total'       => 'integer',
        'adjrw_total'         => 'decimal:4',
        'operation_total'     => 'integer',
        'an_ucs_incup'        => 'integer',
        'an_ucs_outprov'      => 'integer',
        'an_ofc'              => 'integer',
        'an_lgo'              => 'integer',
        'an_sss'              => 'integer',
        'an_other'            => 'integer',
        'admdate_ucs_incup'   => 'integer',
        'admdate_ucs_outprov' => 'integer',
        'admdate_ofc'         => 'integer',
        'admdate_lgo'         => 'integer',
        'admdate_sss'         => 'integer',
        'admdate_other'       => 'integer',
        'adjrw_ucs_incup'     => 'decimal:4',
        'adjrw_ucs_outprov'   => 'decimal:4',
        'adjrw_ofc'           => 'decimal:4',
        'adjrw_lgo'           => 'decimal:4',
        'adjrw_sss'           => 'decimal:4',
        'adjrw_other'         => 'decimal:4',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    /**
     * Relationship with Hospital model.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }
}
