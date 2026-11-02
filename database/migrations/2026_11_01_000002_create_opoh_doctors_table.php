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
        if (!Schema::hasTable('opoh_doctors')) {
            Schema::create('opoh_doctors', function (Blueprint $table) {
                $table->id();
                $table->string('pname', 50)->nullable();
                $table->string('fname', 100);
                $table->string('lname', 100);
                $table->string('doctor_name', 150);
                $table->string('cid', 13)->nullable();
                $table->string('licenseno', 20)->nullable();
                $table->string('spclty_code', 10);
                $table->string('specialty_name', 100);
                $table->string('position', 150)->nullable();
                $table->enum('is_active', ['Y', 'N'])->default('Y');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index(['cid', 'licenseno', 'is_active'], 'idx_doctor_lookup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opoh_doctors');
    }
};

