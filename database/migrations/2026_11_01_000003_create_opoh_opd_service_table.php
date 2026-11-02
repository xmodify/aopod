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
        if (!Schema::hasTable('opoh_opd_service')) {
            Schema::create('opoh_opd_service', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('vstdate');
                $table->string('spclty_code', 10);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('licenseno', 20)->nullable();
                $table->string('specialty_name', 100);
                $table->unsignedInteger('hn_total')->default(0);
                $table->unsignedInteger('visit_total')->default(0);
                $table->unsignedInteger('visit_ucs')->default(0);
                $table->unsignedInteger('visit_ofc')->default(0);
                $table->unsignedInteger('visit_lgo')->default(0);
                $table->unsignedInteger('visit_sss')->default(0);
                $table->unsignedInteger('visit_other')->default(0);
                $table->decimal('inc_total', 12, 2)->default(0.00);
                $table->decimal('inc_drug_total', 12, 2)->default(0.00);
                $table->decimal('inc_lab_total', 12, 2)->default(0.00);
                $table->unsignedInteger('refer_to_origin')->default(0);
                $table->unsignedInteger('admit_ipd')->default(0);
                $table->timestamps();

                $table->unique(['hospcode', 'vstdate', 'spclty_code', 'doctor_id'], 'uk_opoh_opd');
                $table->index('vstdate', 'idx_vstdate');
                $table->index('hospcode', 'idx_hospcode');
                $table->index(['doctor_id', 'licenseno'], 'idx_doctor');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opoh_opd_service');
    }
};

