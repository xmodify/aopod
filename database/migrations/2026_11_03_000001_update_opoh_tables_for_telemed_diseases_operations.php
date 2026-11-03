<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add visit_telemed to opoh_opd_service
        if (Schema::hasTable('opoh_opd_service')) {
            Schema::table('opoh_opd_service', function (Blueprint $table) {
                if (!Schema::hasColumn('opoh_opd_service', 'visit_telemed')) {
                    $table->unsignedInteger('visit_telemed')->default(0)->after('visit_other')->comment('จำนวนครั้งที่ตรวจผ่าน Telemedicine');
                }
            });
        }

        // 2. Create opoh_opd_diagnoses (Top Diseases OPD)
        if (!Schema::hasTable('opoh_opd_diagnoses')) {
            Schema::create('opoh_opd_diagnoses', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('vstdate');
                $table->string('spclty_code', 10);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('icd10', 10);
                $table->string('diag_name', 255);
                $table->unsignedInteger('case_total')->default(1);
                $table->timestamps();

                $table->unique(['hospcode', 'vstdate', 'spclty_code', 'doctor_id', 'icd10'], 'uk_opoh_opd_diag');
                $table->index('vstdate', 'idx_opd_diag_vstdate');
                $table->index('hospcode', 'idx_opd_diag_hospcode');
                $table->index('icd10', 'idx_opd_diag_icd10');
            });
        }

        // 3. Create opoh_ipd_diagnoses (Top Diseases IPD)
        if (!Schema::hasTable('opoh_ipd_diagnoses')) {
            Schema::create('opoh_ipd_diagnoses', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('dchdate');
                $table->string('spclty_code', 10);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('icd10', 10);
                $table->string('diag_name', 255);
                $table->unsignedInteger('case_total')->default(1);
                $table->timestamps();

                $table->unique(['hospcode', 'dchdate', 'spclty_code', 'doctor_id', 'icd10'], 'uk_opoh_ipd_diag');
                $table->index('dchdate', 'idx_ipd_diag_dchdate');
                $table->index('hospcode', 'idx_ipd_diag_hospcode');
                $table->index('icd10', 'idx_ipd_diag_icd10');
            });
        }

        // 4. Create opoh_operation_service (Surgeries & Procedures - Tab 3)
        if (!Schema::hasTable('opoh_operation_service')) {
            Schema::create('opoh_operation_service', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('operation_date');
                $table->string('spclty_code', 10);
                $table->string('specialty_name', 100);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('licenseno', 20)->nullable();
                $table->string('icd9', 10)->nullable();
                $table->string('operation_name', 255);
                $table->string('op_type', 10)->default('IPD')->comment('IPD (ผ่าตัดใหญ่ในตึก) หรือ ODS (ผ่าตัดวันเดียวกลับ/ส่องกล้อง)');
                $table->unsignedInteger('case_total')->default(1);
                $table->decimal('inc_total', 12, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['hospcode', 'operation_date', 'spclty_code', 'doctor_id', 'icd9', 'op_type'], 'uk_opoh_op');
                $table->index('operation_date', 'idx_op_date');
                $table->index('hospcode', 'idx_op_hospcode');
                $table->index('spclty_code', 'idx_op_spclty');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opoh_operation_service');
        Schema::dropIfExists('opoh_ipd_diagnoses');
        Schema::dropIfExists('opoh_opd_diagnoses');

        if (Schema::hasTable('opoh_opd_service') && Schema::hasColumn('opoh_opd_service', 'visit_telemed')) {
            Schema::table('opoh_opd_service', function (Blueprint $table) {
                $table->dropColumn('visit_telemed');
            });
        }
    }
};

