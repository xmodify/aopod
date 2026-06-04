<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Death extends Model
{
    use HasFactory;

    protected $table = 'deaths';

    protected $fillable = [
        'pid',
        'sex',
        'age',
        'ddate',
        'dmon',
        'dyear',
        'drcode',
        'hos_id',
        'lccaattmm',
        'ncause',
        'bdate',
        'bmon',
        'byear',
        'dplace',
        'ghos',
        'codepro',
    ];
}
