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
            // Lookup & Setup
            'school_levels',
            'school_types',
            'staff_categories',
            'staff_designations',
            
            // Session & System
            'login_logs',
            'school_branches',
            'school_contacts',
            'school_documents',
            'school_settings',

            // Academic & Student Sub-tables
            'student_enrollments',
            'student_documents',
            'student_previous_schools',
            'student_status_history',
            'student_siblings',
            'teacher_subjects',
            'student_guardian',

            // Finance Sub-tables
            'invoice_items',
            'fee_payments', // Wait, was it already there?
            
            // Health, Hostel, Library, Transport
            'medical_visits',
            'student_health_records', // Check
            'hostel_allocations',
            'book_copies',
            'book_loans',
            'route_stops',
            'student_transport',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'school_id')) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->foreignId('school_id')->nullable()->after('id')->constrained('schools')->onDelete('cascade');
                    $blueprint->index('school_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback for safety
    }
};
