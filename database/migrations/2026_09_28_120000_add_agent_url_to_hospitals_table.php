<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('hospitals', function (Blueprint $table) {
            if (!Schema::hasColumn('hospitals', 'agent_url')) {
                $table->string('agent_url')->nullable()->after('token_api')->default('http://127.0.0.1:8989');
            }
        });
    }

    public function down(): void {
        Schema::table('hospitals', function (Blueprint $table) {
            if (Schema::hasColumn('hospitals', 'agent_url')) {
                $table->dropColumn('agent_url');
            }
        });
    }
};
