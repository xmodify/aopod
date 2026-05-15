// database/migrations/2025_09_28_102106_create_hospitals_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('hospcode', 10)->unique();
            $table->string('name')->nullable();

            $table->string('token_api')->nullable();
            $table->string('contact')->nullable();

            $table->boolean('is_active')->default(true);

            // รองรับการใช้ Auth แบบ password ในอนาคต
            $table->string('password')->nullable();
            $table->rememberToken()->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hospitals');
    }
};
