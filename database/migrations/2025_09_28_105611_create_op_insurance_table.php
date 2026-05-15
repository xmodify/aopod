<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('op_insurance', function (Blueprint $table) {
            // คีย์อ้างอิง รพ. และวันที่สรุป
            $table->string('hospcode', 10);
            $table->date('vstdate');

            // ตัวเลขสรุปจาก SQL ที่ให้มา
            $table->unsignedInteger('total_visit')->default(0);
            $table->unsignedInteger('endpoint')->default(0);
            $table->unsignedInteger('ofc_visit')->default(0);
            $table->unsignedInteger('ofc_edc')->default(0);
            $table->unsignedInteger('non_authen')->default(0);
            $table->unsignedInteger('non_hmain')->default(0);
            $table->unsignedInteger('uc_anywhere')->default(0);
            $table->unsignedInteger('uc_anywhere_endpoint')->default(0);
            $table->unsignedInteger('uc_cr')->default(0);
            $table->unsignedInteger('uc_cr_endpoint')->default(0);
            $table->unsignedInteger('uc_herb')->default(0);
            $table->unsignedInteger('uc_herb_endpoint')->default(0);
            $table->unsignedInteger('ppfs')->default(0);
            $table->unsignedInteger('ppfs_endpoint')->default(0);
            $table->unsignedInteger('uc_healthmed')->default(0);
            $table->unsignedInteger('uc_healthmed_endpoint')->default(0);

            $table->timestamps();

            // คีย์หลัก: hospcode + vstdate
            $table->primary(['hospcode', 'vstdate']);
            // ดัชนีเสริม (แล้วแต่การค้นหา)
            $table->index('vstdate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('op_insurance');
    }
};