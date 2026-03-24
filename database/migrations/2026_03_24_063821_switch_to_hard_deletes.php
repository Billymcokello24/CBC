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
        $tables = [
            'users', 'students', 'teachers', 'grade_levels', 'streams', 'classes', 
            'academic_years', 'academic_terms', 'departments', 'timetables', 
            'timetable_slots', 'timetable_templates', 'assessments', 'report_cards', 
            'rubrics', 'clubs', 'events', 'fee_structures', 'invoices', 'guardians', 
            'hostels', 'books', 'schools', 'school_branches', 'school_documents', 
            'student_documents', 'vehicles'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                // Permanently delete soft-deleted records first
                DB::table($table)->whereNotNull('deleted_at')->delete();
                
                // Drop the column
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('deleted_at');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to restore soft deletes with data integrity, 
        // but can re-add columns if needed.
        $tables = [
            'users', 'students', 'teachers', 'grade_levels', 'streams', 'classes', 
            'academic_years', 'academic_terms', 'departments', 'timetables', 
            'timetable_slots', 'timetable_templates', 'assessments', 'report_cards', 
            'rubrics', 'clubs', 'events', 'fee_structures', 'invoices', 'guardians', 
            'hostels', 'books', 'schools', 'school_branches', 'school_documents', 
            'student_documents', 'vehicles'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }
};
