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
        'inc_total',
    ];

    protected $casts = [
        'dchdate' => 'date',
        'an_total' => 'integer',
        'admdate_total' => 'integer',
        'adjrw_total' => 'decimal:4',
        'operation_total' => 'integer',
        'inc_total' => 'decimal:2',
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
}

