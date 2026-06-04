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
        Schema::create('deaths', function (Blueprint $table) {
            $table->id();
            $table->string('pid', 13)->index();
            $table->string('sex', 2)->nullable();
            $table->integer('age')->nullable();
            $table->integer('ddate')->nullable();
            $table->integer('dmon')->nullable();
            $table->integer('dyear')->nullable();
            $table->string('drcode', 10)->nullable();
            $table->string('hos_id', 10)->nullable();
            $table->string('lccaattmm', 15)->nullable();
            $table->string('ncause', 10)->nullable();
            $table->integer('bdate')->nullable();
            $table->integer('bmon')->nullable();
            $table->integer('byear')->nullable();
            $table->string('dplace', 50)->nullable();
            $table->string('ghos', 10)->nullable();
            $table->string('codepro', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deaths');
    }
};
