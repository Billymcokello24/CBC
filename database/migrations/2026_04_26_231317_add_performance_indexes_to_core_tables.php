<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes one by one and ignore duplicates if they already exist
        // Using specific names to avoid auto-naming conflicts
        
        try {
            Schema::table('students', function (Blueprint $table) {
                $table->index('gender', 'idx_students_gender');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('students', function (Blueprint $table) {
                $table->index('boarding_status', 'idx_students_boarding_status');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('students', function (Blueprint $table) {
                $table->index('admission_date', 'idx_students_admission_date');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('students', function (Blueprint $table) {
                $table->index('county', 'idx_students_county');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('teachers', function (Blueprint $table) {
                $table->index('id_number', 'idx_teachers_id_number');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('teachers', function (Blueprint $table) {
                $table->index('phone', 'idx_teachers_phone');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('scheme_entries', function (Blueprint $table) {
                $table->index(['scheme_id', 'week_number', 'lesson_number'], 'idx_scheme_week_lesson');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lesson_plans', function (Blueprint $table) {
                $table->index('status', 'idx_lesson_plans_status');
            });
        } catch (\Exception $e) {}
        
        try {
            Schema::table('lesson_plans', function (Blueprint $table) {
                $table->index('lesson_date', 'idx_lesson_plans_date');
            });
        } catch (\Exception $e) {}
    }

    public function down(): void
    {
        try {
            Schema::table('students', function (Blueprint $table) {
                $table->dropIndex('idx_students_gender');
                $table->dropIndex('idx_students_boarding_status');
                $table->dropIndex('idx_students_admission_date');
                $table->dropIndex('idx_students_county');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropIndex('idx_teachers_id_number');
                $table->dropIndex('idx_teachers_phone');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('scheme_entries', function (Blueprint $table) {
                $table->dropIndex('idx_scheme_week_lesson');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lesson_plans', function (Blueprint $table) {
                $table->dropIndex('idx_lesson_plans_status');
                $table->dropIndex('idx_lesson_plans_date');
            });
        } catch (\Exception $e) {}
    }
};
