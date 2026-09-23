<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentSchedule extends Model
{
    protected $table = 'agent_schedules';

    protected $fillable = [
        'hospcode',
        'interval_hours',
        'start_minute',
        'opd_days_back',
        'ipd_days_back',
        'bed_interval_mins',
        'schedule_type',
        'interval_mins',
        'daily_hour',
        'daily_minute',
        'sync_days_back',
        'is_active',
    ];

    protected $casts = [
        'interval_hours'    => 'integer',
        'start_minute'      => 'integer',
        'opd_days_back'     => 'integer',
        'ipd_days_back'     => 'integer',
        'bed_interval_mins' => 'integer',
        'interval_mins'     => 'integer',
        'daily_hour'        => 'integer',
        'daily_minute'      => 'integer',
        'sync_days_back'    => 'integer',
        'is_active'         => 'boolean',
    ];

    /**
     * Get active schedule for a specific hospital or global fallback.
     *
     * @param string|null $hospcode
     * @return static
     */
    public static function getForHospital(?string $hospcode = null)
    {
        // 1. Try hospital-specific override
        if ($hospcode) {
            $specific = static::where('hospcode', $hospcode)
                ->where('is_active', true)
                ->first();
            if ($specific) {
                return $specific;
            }
        }

        // 2. Fallback to GLOBAL 'ALL' schedule
        $global = static::where('hospcode', 'ALL')
            ->first();
        if ($global) {
            return $global;
        }

        // 3. Fallback in-memory default object
        $default = new static([
            'hospcode'          => 'ALL',
            'interval_hours'    => 1,
            'start_minute'      => 15,
            'opd_days_back'     => 5,
            'ipd_days_back'     => 30,
            'bed_interval_mins' => 15,
            'is_active'         => true,
        ]);

        return $default;
    }
}
