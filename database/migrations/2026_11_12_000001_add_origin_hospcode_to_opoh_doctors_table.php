<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มคอลัมน์ origin_hospcode ให้ตาราง opoh_doctors เพื่อระบุรพ.ต้นสังกัดของแพทย์
     * (ซิงค์โครงสร้างให้ตรงกับ docs/extracted_schemas.json สำหรับติดตั้งใหม่และอัปเกรด)
     */
    public function up(): void
    {
        if (Schema::hasTable('opoh_doctors') && !Schema::hasColumn('opoh_doctors', 'origin_hospcode')) {
            Schema::table('opoh_doctors', function (Blueprint $table) {
                $table->string('origin_hospcode', 10)->nullable()->after('position');
                $table->index('origin_hospcode', 'idx_doctor_origin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('opoh_doctors') && Schema::hasColumn('opoh_doctors', 'origin_hospcode')) {
            Schema::table('opoh_doctors', function (Blueprint $table) {
                $table->dropIndex('idx_doctor_origin');
                $table->dropColumn('origin_hospcode');
            });
        }
    }
};

