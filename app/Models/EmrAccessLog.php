<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrAccessLog extends Model
{
    use HasFactory;

    protected $table = 'emr_access_logs';

    const UPDATED_AT = null; // Append-only audit table

    protected $fillable = [
        'user_id',
        'username',
        'user_name',
        'user_role',
        'user_hospcode',
        'provider_id',
        'action',
        'target_cid',
        'target_hn',
        'target_vn',
        'target_hospcode',
        'reason',
        'ip_address',
        'user_agent',
        'request_url',
        'http_method',
        'status',
        'http_status_code',
        'response_time_ms',
        'record_count',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'created_at'        => 'datetime',
        'http_status_code'  => 'integer',
        'response_time_ms'  => 'integer',
        'record_count'      => 'integer',
    ];

    /**
     * Relationship with the User who initiated the query.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with the User's home Hospital.
     */
    public function userHospital()
    {
        return $this->belongsTo(Hospital::class, 'user_hospcode', 'hospcode');
    }

    /**
     * Relationship with the Target Hospital where patient data originated.
     */
    public function targetHospital()
    {
        return $this->belongsTo(Hospital::class, 'target_hospcode', 'hospcode');
    }

    /**
     * Helper to mask CID for privacy displays (e.g. 1-1234-XXXXX-12-3).
     */
    public function getMaskedCidAttribute(): string
    {
        if (empty($this->target_cid) || strlen($this->target_cid) !== 13) {
            return $this->target_cid ?? '-';
        }
        return substr($this->target_cid, 0, 4) . 'XXXXX' . substr($this->target_cid, 9, 4);
    }
}
