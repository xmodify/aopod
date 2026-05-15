<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; // สำคัญ: ใช้แทน Model
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hospital extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'hospcode',
        'name',
        'token_api',
        'contact',
        'is_active',   // อย่าลืมเผื่อ column นี้ด้วย
    ];

    protected $hidden = [
        'password', // เผื่อใช้ auth แบบ password ในอนาคต
        'remember_token',
    ];

    protected $casts = [
    'is_active' => 'boolean',
    ];

    // ถ้าใช้ hospcode เป็นตัว login
    public function getAuthIdentifierName()
    {
        return 'hospcode';
    }
}
