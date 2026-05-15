<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ipd extends Model
{
    protected $table = 'ipd';

    protected $fillable = [
        'hospcode', 'dchdate','an_total', 'admdate', 
        
        'bed_occupancy', 'active_bed', 'cmi', 'adjrw', 
        'inc_total', 'inc_lab_total', 'inc_drug_total',        
    ];

    protected $casts = [
        'dchdate' => 'date:Y-m-d',

        // Visits -> int
        'an_total'          => 'int',
        'admdate'           => 'int',        

        // Income -> float/double
        'bed_occupancy'     => 'float',
        'active_bed'        => 'float',
        'cmi'               => 'float',        
        'adjrw'             => 'float',
        'inc_total'            => 'float',
        'inc_lab_total'        => 'float',
        'inc_drug_total'       => 'float',      
    ];
}
