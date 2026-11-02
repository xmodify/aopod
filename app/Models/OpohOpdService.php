<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohOpdService extends Model
{
    use HasFactory;

    protected $table = 'opoh_opd_service';

    protected $fillable = [
        'hospcode',
        'vstdate',
        'spclty_code',
        'doctor_id',
        'licenseno',
        'specialty_name',
        'hn_total',
        'visit_total',
        'visit_ucs',
        'visit_ofc',
        'visit_lgo',
        'visit_sss',
        'visit_other',
        'inc_total',
        'inc_drug_total',
        'inc_lab_total',
        'refer_to_origin',
        'admit_ipd',
    ];

    protected $casts = [
        'vstdate' => 'date',
        'hn_total' => 'integer',
        'visit_total' => 'integer',
        'visit_ucs' => 'integer',
        'visit_ofc' => 'integer',
        'visit_lgo' => 'integer',
        'visit_sss' => 'integer',
        'visit_other' => 'integer',
        'inc_total' => 'decimal:2',
        'inc_drug_total' => 'decimal:2',
        'inc_lab_total' => 'decimal:2',
        'refer_to_origin' => 'integer',
        'admit_ipd' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Hospital model.
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }

    public function doctor()
    {
        return $this->belongsTo(OpohDoctor::class, 'doctor_id');
    }
}

