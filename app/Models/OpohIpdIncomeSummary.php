<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohIpdIncomeSummary extends Model
{
    use HasFactory;

    protected $table = 'opoh_ipd_income_summaries';

    protected $fillable = [
        'hospcode',
        'dchdate',
        'drg_chrgitem_id',
        'drg_chrgitem_name',
        'inc_ucs_incup',
        'inc_ucs_outprov',
        'inc_ofc',
        'inc_lgo',
        'inc_sss',
        'inc_other',
        'inc_total',
    ];

    protected $casts = [
        'dchdate'         => 'date',
        'drg_chrgitem_id' => 'integer',
        'inc_ucs_incup'   => 'decimal:2',
        'inc_ucs_outprov' => 'decimal:2',
        'inc_ofc'         => 'decimal:2',
        'inc_lgo'         => 'decimal:2',
        'inc_sss'         => 'decimal:2',
        'inc_other'       => 'decimal:2',
        'inc_total'       => 'decimal:2',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospcode', 'hospcode');
    }
}

