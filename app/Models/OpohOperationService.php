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
        'adjrw_total',
        'case_ucs_incup',
        'case_ucs_outprov',
        'case_ofc',
        'case_lgo',
        'case_sss',
        'case_other',
    ];

    protected $casts = [
        'operation_date'  => 'date',
        'case_total'      => 'integer',
        'adjrw_total'     => 'decimal:4',
        'case_ucs_incup'  => 'integer',
        'case_ucs_outprov'=> 'integer',
        'case_ofc'        => 'integer',
        'case_lgo'        => 'integer',
        'case_sss'        => 'integer',
        'case_other'      => 'integer',
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
