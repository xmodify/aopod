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
        Schema::table('opoh_opd_service', function (Blueprint $table) {
            if (!Schema::hasColumn('opoh_opd_service', 'visit_ucs_incup')) {
                $table->unsignedInteger('visit_ucs_incup')->default(0)->after('visit_telemed');
                $table->unsignedInteger('visit_ucs_outprov')->default(0)->after('visit_ucs_incup');
                $table->decimal('inc_ucs_incup', 12, 2)->default(0.00)->after('inc_lab_total');
                $table->decimal('inc_ucs_outprov', 12, 2)->default(0.00)->after('inc_ucs_incup');
                $table->decimal('inc_ofc', 12, 2)->default(0.00)->after('inc_ucs_outprov');
                $table->decimal('inc_lgo', 12, 2)->default(0.00)->after('inc_ofc');
                $table->decimal('inc_sss', 12, 2)->default(0.00)->after('inc_lgo');
                $table->decimal('inc_other', 12, 2)->default(0.00)->after('inc_sss');
            }
        });

        Schema::table('opoh_ipd_service', function (Blueprint $table) {
            if (!Schema::hasColumn('opoh_ipd_service', 'an_ucs_incup')) {
                $table->unsignedInteger('an_ucs_incup')->default(0)->after('inc_total');
                $table->unsignedInteger('an_ucs_outprov')->default(0)->after('an_ucs_incup');
                $table->unsignedInteger('an_ofc')->default(0)->after('an_ucs_outprov');
                $table->unsignedInteger('an_lgo')->default(0)->after('an_ofc');
                $table->unsignedInteger('an_sss')->default(0)->after('an_lgo');
                $table->unsignedInteger('an_other')->default(0)->after('an_sss');

                $table->unsignedInteger('admdate_ucs_incup')->default(0)->after('an_other');
                $table->unsignedInteger('admdate_ucs_outprov')->default(0)->after('admdate_ucs_incup');
                $table->unsignedInteger('admdate_ofc')->default(0)->after('admdate_ucs_outprov');
                $table->unsignedInteger('admdate_lgo')->default(0)->after('admdate_ofc');
                $table->unsignedInteger('admdate_sss')->default(0)->after('admdate_lgo');
                $table->unsignedInteger('admdate_other')->default(0)->after('admdate_sss');

                $table->decimal('adjrw_ucs_incup', 10, 4)->default(0.0000)->after('admdate_other');
                $table->decimal('adjrw_ucs_outprov', 10, 4)->default(0.0000)->after('adjrw_ucs_incup');
                $table->decimal('adjrw_ofc', 10, 4)->default(0.0000)->after('adjrw_ucs_outprov');
                $table->decimal('adjrw_lgo', 10, 4)->default(0.0000)->after('adjrw_ofc');
                $table->decimal('adjrw_sss', 10, 4)->default(0.0000)->after('adjrw_lgo');
                $table->decimal('adjrw_other', 10, 4)->default(0.0000)->after('adjrw_sss');

                $table->decimal('inc_ucs_incup', 12, 2)->default(0.00)->after('adjrw_other');
                $table->decimal('inc_ucs_outprov', 12, 2)->default(0.00)->after('inc_ucs_incup');
                $table->decimal('inc_ofc', 12, 2)->default(0.00)->after('inc_ucs_outprov');
                $table->decimal('inc_lgo', 12, 2)->default(0.00)->after('inc_ofc');
                $table->decimal('inc_sss', 12, 2)->default(0.00)->after('inc_lgo');
                $table->decimal('inc_other', 12, 2)->default(0.00)->after('inc_sss');
            }
        });

        Schema::table('opoh_operation_service', function (Blueprint $table) {
            if (!Schema::hasColumn('opoh_operation_service', 'adjrw_total')) {
                $table->decimal('adjrw_total', 10, 4)->default(0.0000)->after('inc_total');
                $table->unsignedInteger('case_ucs_incup')->default(0)->after('adjrw_total');
                $table->unsignedInteger('case_ucs_outprov')->default(0)->after('case_ucs_incup');
                $table->unsignedInteger('case_ofc')->default(0)->after('case_ucs_outprov');
                $table->unsignedInteger('case_lgo')->default(0)->after('case_ofc');
                $table->unsignedInteger('case_sss')->default(0)->after('case_lgo');
                $table->unsignedInteger('case_other')->default(0)->after('case_sss');

                $table->decimal('inc_ucs_incup', 12, 2)->default(0.00)->after('case_other');
                $table->decimal('inc_ucs_outprov', 12, 2)->default(0.00)->after('inc_ucs_incup');
                $table->decimal('inc_ofc', 12, 2)->default(0.00)->after('inc_ucs_outprov');
                $table->decimal('inc_lgo', 12, 2)->default(0.00)->after('inc_ofc');
                $table->decimal('inc_sss', 12, 2)->default(0.00)->after('inc_lgo');
                $table->decimal('inc_other', 12, 2)->default(0.00)->after('inc_sss');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opoh_opd_service', function (Blueprint $table) {
            $table->dropColumn([
                'visit_ucs_incup', 'visit_ucs_outprov',
                'inc_ucs_incup', 'inc_ucs_outprov', 'inc_ofc', 'inc_lgo', 'inc_sss', 'inc_other'
            ]);
        });

        Schema::table('opoh_ipd_service', function (Blueprint $table) {
            $table->dropColumn([
                'an_ucs_incup', 'an_ucs_outprov', 'an_ofc', 'an_lgo', 'an_sss', 'an_other',
                'admdate_ucs_incup', 'admdate_ucs_outprov', 'admdate_ofc', 'admdate_lgo', 'admdate_sss', 'admdate_other',
                'adjrw_ucs_incup', 'adjrw_ucs_outprov', 'adjrw_ofc', 'adjrw_lgo', 'adjrw_sss', 'adjrw_other',
                'inc_ucs_incup', 'inc_ucs_outprov', 'inc_ofc', 'inc_lgo', 'inc_sss', 'inc_other'
            ]);
        });

        Schema::table('opoh_operation_service', function (Blueprint $table) {
            $table->dropColumn([
                'adjrw_total',
                'case_ucs_incup', 'case_ucs_outprov', 'case_ofc', 'case_lgo', 'case_sss', 'case_other',
                'inc_ucs_incup', 'inc_ucs_outprov', 'inc_ofc', 'inc_lgo', 'inc_sss', 'inc_other'
            ]);
        });
    }
};

