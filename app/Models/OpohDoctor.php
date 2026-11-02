<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpohDoctor extends Model
{
    use HasFactory;

    protected $table = 'opoh_doctors';

    protected $fillable = [
        'pname',
        'fname',
        'lname',
        'doctor_name',
        'cid',
        'licenseno',
        'spclty_code',
        'specialty_name',
        'position',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for active doctors only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 'Y');
    }

    /**
     * Relationship with User creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

