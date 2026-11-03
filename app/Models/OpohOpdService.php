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
        'visit_telemed',
        'visit_ucs_incup',
        'visit_ucs_outprov',
        'inc_total',
        'inc_drug_total',
        'inc_lab_total',
        'inc_ucs_incup',
        'inc_ucs_outprov',
        'inc_ofc',
        'inc_lgo',
        'inc_sss',
        'inc_other',
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
        'visit_telemed' => 'integer',
        'visit_ucs_incup' => 'integer',
        'visit_ucs_outprov' => 'integer',
        'inc_total' => 'decimal:2',
        'inc_drug_total' => 'decimal:2',
        'inc_lab_total' => 'decimal:2',
        'inc_ucs_incup' => 'decimal:2',
        'inc_ucs_outprov' => 'decimal:2',
        'inc_ofc' => 'decimal:2',
        'inc_lgo' => 'decimal:2',
        'inc_sss' => 'decimal:2',
        'inc_other' => 'decimal:2',
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

