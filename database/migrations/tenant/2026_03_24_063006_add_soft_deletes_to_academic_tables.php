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
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('period_definitions', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('school_subjects', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::table('timetable_templates', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('period_definitions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('school_subjects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('timetable_slots', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('timetable_templates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
