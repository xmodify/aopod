<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohOperationService extends Model
{
    use HasFactory;

    protected $table = 'opoh_operation_service';

    protected $fillable = [
        'hospcode',
        'operation_date',
        'spclty_code',
        'specialty_name',
        'doctor_id',
        'licenseno',
        'icd9',
        'operation_name',
        'op_type',
        'case_total',
        'inc_total',
        'adjrw_total',
        'case_ucs_incup',
        'case_ucs_outprov',
        'case_ofc',
        'case_lgo',
        'case_sss',
        'case_other',
        'inc_ucs_incup',
        'inc_ucs_outprov',
        'inc_ofc',
        'inc_lgo',
        'inc_sss',
        'inc_other',
    ];

    protected $casts = [
        'operation_date'  => 'date',
        'case_total'      => 'integer',
        'inc_total'       => 'decimal:2',
        'adjrw_total'     => 'decimal:4',
        'case_ucs_incup'  => 'integer',
        'case_ucs_outprov'=> 'integer',
        'case_ofc'        => 'integer',
        'case_lgo'        => 'integer',
        'case_sss'        => 'integer',
        'case_other'      => 'integer',
        'inc_ucs_incup'   => 'decimal:2',
        'inc_ucs_outprov' => 'decimal:2',
        'inc_ofc'         => 'decimal:2',
        'inc_lgo'         => 'decimal:2',
        'inc_sss'         => 'decimal:2',
        'inc_other'       => 'decimal:2',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }

    public function doctor()
    {
        return $this->belongsTo(OpohDoctor::class, 'doctor_id');
    }
}

