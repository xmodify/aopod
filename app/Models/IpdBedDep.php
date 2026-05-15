<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpdBedDep extends Model
{
    // 🧩 ชื่อตารางในฐานข้อมูล
    protected $table = 'ipd_bed_dep';

    // 🔑 primary key (Laravel รองรับแค่ single key แต่เราจะกำหนดเองไว้ใช้ใน query)
    protected $primaryKey = ['hospcode', 'bed_code'];
    public $incrementing = false;

    // 🚫 ปิด timestamps อัตโนมัติ (ไม่มี created_at, updated_at แบบ Laravel)
    public $timestamps = false;

    // 🧾 กำหนด field ที่อนุญาตให้ fill
    protected $fillable = [
        'hospcode',
        'bed_code',
        'bed_qty',
        'bed_use',
        'updated_at',
    ];

    // 🕒 แปลงค่า updated_at ให้เป็น datetime (อ่านง่าย)
    protected $casts = [
        'updated_at' => 'datetime',
    ];
}
