<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $table = 'operation';

    protected $fillable = [
        'hospcode',
        'vstdate',
        'visit_operation',
    ];

    protected $casts = [
        'vstdate' => 'date:Y-m-d',
        'visit_operation' => 'int',
    ];
}
