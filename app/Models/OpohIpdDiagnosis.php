<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohIpdDiagnosis extends Model
{
    use HasFactory;

    protected $table = 'opoh_ipd_diagnoses';

    protected $fillable = [
        'hospcode',
        'dchdate',
        'spclty_code',
        'doctor_id',
        'icd10',
        'diag_name',
        'case_total',
    ];

    protected $casts = [
        'dchdate'    => 'date',
        'case_total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

