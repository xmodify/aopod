<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ปรับโครงสร้าง OPOH ใน migration ให้ตรงกับ docs/extracted_schemas.json:
     * 1. สร้าง opoh_income_categories หากยังไม่มี (กรณีติดตั้งใหม่)
     * 2. ลบคอลัมน์ inc_* เดิมใน opoh_ipd_service ให้ตรงกับ schema ปัจจุบัน
     * 3. สร้างตารางสรุปผู้ป่วยในระดับโรงพยาบาลแบบไม่นับซ้ำราย AN (opoh_ipd_hospital_summaries, opoh_ipd_income_summaries)
     */
    public function up(): void
    {
        if (!Schema::hasTable('opoh_income_categories')) {
            Schema::create('opoh_income_categories', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->string('service_type', 10)->default('OPD');
                $table->date('vstdate');
                $table->string('spclty_code', 10);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('licenseno', 20)->nullable();
                $table->integer('drg_chrgitem_id');
                $table->string('drg_chrgitem_name', 150);
                $table->decimal('inc_ucs_incup', 12, 2)->default(0.00);
                $table->decimal('inc_ucs_outprov', 12, 2)->default(0.00);
                $table->decimal('inc_ofc', 12, 2)->default(0.00);
                $table->decimal('inc_lgo', 12, 2)->default(0.00);
                $table->decimal('inc_sss', 12, 2)->default(0.00);
                $table->decimal('inc_other', 12, 2)->default(0.00);
                $table->decimal('inc_total', 12, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['hospcode', 'service_type', 'vstdate', 'doctor_id', 'drg_chrgitem_id'], 'uk_opoh_inc_cat');
                $table->index('vstdate', 'idx_vstdate');
                $table->index('hospcode', 'idx_hospcode');
                $table->index('doctor_id', 'idx_doctor');
                $table->index('spclty_code', 'idx_spclty');
            });
        }

        if (Schema::hasTable('opoh_ipd_service')) {
            $legacyCols = [
                'inc_total',
                'inc_ucs_incup',
                'inc_ucs_outprov',
                'inc_ofc',
                'inc_lgo',
                'inc_sss',
                'inc_other',
            ];
            $dropCols = array_values(array_filter($legacyCols, fn($c) => Schema::hasColumn('opoh_ipd_service', $c)));
            if (!empty($dropCols)) {
                Schema::table('opoh_ipd_service', function (Blueprint $table) use ($dropCols) {
                    $table->dropColumn($dropCols);
                });
            }
        }

        if (!Schema::hasTable('opoh_ipd_hospital_summaries')) {
            Schema::create('opoh_ipd_hospital_summaries', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('dchdate');
                $table->unsignedInteger('an_total')->default(0);
                $table->unsignedInteger('admdate_total')->default(0);
                $table->decimal('adjrw_total', 10, 4)->default(0.0000);
                $table->unsignedInteger('operation_total')->default(0);

                $table->unsignedInteger('an_ucs_incup')->default(0);
                $table->unsignedInteger('an_ucs_outprov')->default(0);
                $table->unsignedInteger('an_ofc')->default(0);
                $table->unsignedInteger('an_lgo')->default(0);
                $table->unsignedInteger('an_sss')->default(0);
                $table->unsignedInteger('an_other')->default(0);

                $table->unsignedInteger('admdate_ucs_incup')->default(0);
                $table->unsignedInteger('admdate_ucs_outprov')->default(0);
                $table->unsignedInteger('admdate_ofc')->default(0);
                $table->unsignedInteger('admdate_lgo')->default(0);
                $table->unsignedInteger('admdate_sss')->default(0);
                $table->unsignedInteger('admdate_other')->default(0);

                $table->decimal('adjrw_ucs_incup', 10, 4)->default(0.0000);
                $table->decimal('adjrw_ucs_outprov', 10, 4)->default(0.0000);
                $table->decimal('adjrw_ofc', 10, 4)->default(0.0000);
                $table->decimal('adjrw_lgo', 10, 4)->default(0.0000);
                $table->decimal('adjrw_sss', 10, 4)->default(0.0000);
                $table->decimal('adjrw_other', 10, 4)->default(0.0000);
                $table->timestamps();

                $table->unique(['hospcode', 'dchdate'], 'uk_opoh_ipd_hosp_sum');
                $table->index('dchdate', 'idx_opoh_ipd_hosp_sum_dchdate');
            });
        }

        if (!Schema::hasTable('opoh_ipd_income_summaries')) {
            Schema::create('opoh_ipd_income_summaries', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('dchdate');
                $table->integer('drg_chrgitem_id');
                $table->string('drg_chrgitem_name', 150);
                $table->decimal('inc_ucs_incup', 12, 2)->default(0.00);
                $table->decimal('inc_ucs_outprov', 12, 2)->default(0.00);
                $table->decimal('inc_ofc', 12, 2)->default(0.00);
                $table->decimal('inc_lgo', 12, 2)->default(0.00);
                $table->decimal('inc_sss', 12, 2)->default(0.00);
                $table->decimal('inc_other', 12, 2)->default(0.00);
                $table->decimal('inc_total', 12, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['hospcode', 'dchdate', 'drg_chrgitem_id'], 'uk_opoh_ipd_inc_sum');
                $table->index('dchdate', 'idx_opoh_ipd_inc_sum_dchdate');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opoh_ipd_income_summaries');
        Schema::dropIfExists('opoh_ipd_hospital_summaries');
    }
};

