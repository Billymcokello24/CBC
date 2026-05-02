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
        // Add school_id to student_competency_ratings
        if (Schema::hasTable('student_competency_ratings') && !Schema::hasColumn('student_competency_ratings', 'school_id')) {
            Schema::table('student_competency_ratings', function (Blueprint $table) {
                $table->foreignId('school_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        // Add school_id to report_cards
        if (Schema::hasTable('report_cards') && !Schema::hasColumn('report_cards', 'school_id')) {
            Schema::table('report_cards', function (Blueprint $table) {
                $table->foreignId('school_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            });
        }

        // Add school_id to student_assessment_ratings
        if (Schema::hasTable('student_assessment_ratings') && !Schema::hasColumn('student_assessment_ratings', 'school_id')) {
            Schema::table('student_assessment_ratings', function (Blueprint $table) {
                $table->foreignId('school_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('student_competency_ratings')) {
            Schema::table('student_competency_ratings', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }

        if (Schema::hasTable('report_cards')) {
            Schema::table('report_cards', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }

        if (Schema::hasTable('student_assessment_ratings')) {
            Schema::table('student_assessment_ratings', function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }
    }
};
