<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpdBedType extends Model
{
    protected $table = 'ipd_bed_type';
    protected $primaryKey = 'bed_code';
    
    // Primary key is string and non-incrementing
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = false;

    protected $fillable = [
        'bed_code',
        'bed_name',
        'unit',
    ];
}
