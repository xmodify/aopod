<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A-EMR: ตาราง aemr_access_logs (Audit Trail) และคอลัมน์ users.allow_aemr
 *  - ติดตั้งใหม่: สร้างให้
 *  - ฐานข้อมูลเดิมที่ยังใช้ชื่อ emr_access_logs / allow_emr: เปลี่ยนชื่อโดยคงข้อมูลไว้
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('aemr_access_logs')) {
            if (Schema::hasTable('emr_access_logs')) {
                Schema::rename('emr_access_logs', 'aemr_access_logs');
            } else {
                Schema::create('aemr_access_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable()->index('idx_user_id');
                    $table->string('username', 100)->nullable();
                    $table->string('user_name', 255);
                    $table->string('user_role', 50)->default('staff');
                    $table->string('user_hospcode', 10)->nullable();
                    $table->string('provider_id', 100)->nullable();
                    $table->string('action', 50)->default('SEARCH_CID')->index('idx_action');
                    $table->string('target_cid', 13)->nullable()->index('idx_target_cid');
                    $table->string('target_hn', 50)->nullable();
                    $table->string('target_vn', 50)->nullable();
                    $table->string('target_hospcode', 10)->nullable();
                    $table->string('reason', 255)->default('ตรวจรักษาผู้ป่วย');
                    $table->string('ip_address', 45)->default('127.0.0.1');
                    $table->text('user_agent')->nullable();
                    $table->string('request_url', 500)->nullable();
                    $table->string('http_method', 10)->default('POST');
                    $table->string('status', 20)->default('SUCCESS');
                    $table->integer('http_status_code')->default(200);
                    $table->integer('response_time_ms')->nullable()->default(0);
                    $table->integer('record_count')->nullable()->default(0);
                    $table->text('error_message')->nullable();
                    $table->timestamp('created_at')->useCurrent()->index('idx_created_at');
                });
            }
        }

        if (!Schema::hasColumn('users', 'allow_aemr')) {
            if (Schema::hasColumn('users', 'allow_emr')) {
                Schema::table('users', fn (Blueprint $table) => $table->renameColumn('allow_emr', 'allow_aemr'));
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->boolean('allow_aemr')->default(false)->after('allow_birth');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aemr_access_logs');

        if (Schema::hasColumn('users', 'allow_aemr')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('allow_aemr'));
        }
    }
};
