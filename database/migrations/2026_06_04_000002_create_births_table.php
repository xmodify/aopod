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
        Schema::create('births', function (Blueprint $table) {
            $table->id();
            $table->string('prov', 10)->nullable();
            $table->string('amp', 10)->nullable();
            $table->string('tb', 10)->nullable();
            $table->string('sex', 2)->nullable();
            $table->integer('byear')->nullable();
            $table->integer('bmon')->nullable();
            $table->integer('bdate')->nullable();
            $table->string('nat', 10)->nullable();
            $table->integer('no')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('mage')->nullable();
            $table->string('ket', 10)->nullable();
            $table->integer('yptell')->nullable();
            $table->integer('mptell')->nullable();
            $table->integer('dptell')->nullable();
            $table->text('maddr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('births');
    }
};
