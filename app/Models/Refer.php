<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refer extends Model
{
    protected $table = 'refer';

    protected $fillable = [
        'hospcode',
        'vstdate',
        'visit_referout_inprov',
        'visit_referout_outprov',
        'visit_referout_inprov_ipd',
        'visit_referout_outprov_ipd',
        'visit_referin_inprov',
        'visit_referin_outprov',
        'visit_referin_inprov_ipd',
        'visit_referin_outprov_ipd',
        'visit_referback_inprov',
        'visit_referback_outprov',
    ];

    protected $casts = [
        'vstdate' => 'date:Y-m-d',
        'visit_referout_inprov'      => 'int',
        'visit_referout_outprov'     => 'int',
        'visit_referout_inprov_ipd'  => 'int',
        'visit_referout_outprov_ipd' => 'int',
        'visit_referin_inprov'       => 'int',
        'visit_referin_outprov'      => 'int',
        'visit_referin_inprov_ipd'   => 'int',
        'visit_referin_outprov_ipd'  => 'int',
        'visit_referback_inprov'     => 'int',
        'visit_referback_outprov'    => 'int',
    ];
}
