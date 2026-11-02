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
        if (!Schema::hasTable('opoh_ipd_service')) {
            Schema::create('opoh_ipd_service', function (Blueprint $table) {
                $table->id();
                $table->string('hospcode', 10);
                $table->date('dchdate');
                $table->string('spclty_code', 10);
                $table->unsignedBigInteger('doctor_id')->nullable();
                $table->string('licenseno', 20)->nullable();
                $table->string('specialty_name', 100);
                $table->unsignedInteger('an_total')->default(0);
                $table->unsignedInteger('admdate_total')->default(0);
                $table->decimal('adjrw_total', 10, 4)->default(0.0000);
                $table->unsignedInteger('operation_total')->default(0);
                $table->decimal('inc_total', 12, 2)->default(0.00);
                $table->timestamps();

                $table->unique(['hospcode', 'dchdate', 'spclty_code', 'doctor_id'], 'uk_opoh_ipd');
                $table->index('doctor_id', 'idx_ipd_doctor');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opoh_ipd_service');
    }
};

