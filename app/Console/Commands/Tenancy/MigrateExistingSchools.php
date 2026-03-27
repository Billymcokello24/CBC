<?php

namespace App\Console\Commands\Tenancy;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateExistingSchools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:migrate-existing {--school= : Optional school code to migrate only one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing schools and their data from central to tenant databases';

    /**
     * List of tables with school_id to migrate.
     */
    protected $tables = [
        'academic_calendar_events', 'academic_terms', 'academic_years', 'analytics_reports', 'announcements', 
        'api_keys', 'assessment_types', 'assessments', 'assignments', 'audit_logs', 'behavior_categories', 
        'behavior_interventions', 'behavior_records', 'book_categories', 'book_copies', 'book_loans', 'books', 
        'career_resources', 'classes', 'club_memberships', 'clubs', 'competencies', 'competency_indicators', 
        'competition_results', 'conversations', 'counseling_sessions', 'courses', 'data_snapshots', 'departments', 
        'disciplinary_action_types', 'drivers', 'e_resources', 'email_messages', 'event_types', 'event_venues', 
        'events', 'expense_categories', 'expenses', 'external_system_mappings', 'extracurricular_activities', 
        'fee_categories', 'fee_structures', 'first_aid_logs', 'grade_descriptors', 'grade_levels', 'grading_scales', 
        'guardians', 'health_screenings', 'hostel_allocations', 'hostel_fees', 'hostels', 'impersonation_logs', 
        'incident_reports', 'integration_configs', 'integration_logs', 'interest_assessments', 'invoice_items', 
        'invoices', 'learning_areas', 'leave_types', 'lesson_plans', 'library_cards', 'library_settings', 
        'login_logs', 'medical_conditions', 'medical_visits', 'mentorship_programs', 'message_templates', 
        'messages', 'notifications', 'payment_methods', 'payments', 'performance_metrics', 'period_definitions', 
        'portfolio_categories', 'portfolios', 'report_cards', 'report_schedules', 'room_types', 'rooms', 
        'route_stops', 'rubric_criteria', 'rubric_levels', 'rubrics', 'scheduled_jobs', 'schemes_of_work', 
        'school_branches', 'school_calendar_events', 'school_contacts', 'school_documents', 'school_holidays', 
        'school_levels', 'school_settings', 'school_subjects', 'school_types', 'sms_messages', 'staff_categories', 
        'staff_designations', 'strands', 'streams', 'student_achievements', 'student_assessments', 'student_attendance', 
        'student_documents', 'student_enrollments', 'student_guardian', 'student_health_records', 
        'student_previous_schools', 'student_siblings', 'student_status_history', 'student_transport', 'students', 
        'sub_strands', 'subjects', 'substitutions', 'talent_categories', 'teacher_attendance', 'teacher_subjects', 
        'teachers', 'timetable_templates', 'timetables', 'transport_routes', 'trend_analyses', 'users', 
        'vehicles', 'webhook_endpoints'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $schoolCode = $this->option('school');
        $query = School::query();
        
        if ($schoolCode) {
            $query->where('code', $schoolCode);
        } else {
            $query->whereNull('tenant_id');
        }

        $schools = $query->get();

        if ($schools->isEmpty()) {
            $this->info('No schools found needing migration.');
            return;
        }

        foreach ($schools as $school) {
            $this->info("--------------------------------------------------");
            $this->info("Migrating school: {$school->name} ({$school->code})");

            $tenantId = $school->code;

            // 1. Create Tenant if not exists
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $tenant = Tenant::create([
                    'id' => $tenantId,
                    'name' => $school->name,
                ]);
                $this->line("  - Created Tenant: {$tenantId}");
            }

            // 2. Create Domain if not exists
            $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
            $domain = $tenantId . '.' . $centralDomain;
            if (!$tenant->domains()->where('domain', $domain)->exists()) {
                $tenant->domains()->create(['domain' => $domain]);
                $this->line("  - Created Domain: {$domain}");
            }

            // 3. Update central School record
            $school->update(['tenant_id' => $tenant->id]);

            // 4. Run tenant migrations (if not already run)
            $this->line("  - Running tenant migrations...");
            $this->call('tenants:migrate', ['--tenants' => [$tenant->id]]);

            // 5. Transfer Data
            $tenant->run(function () use ($school) {
                foreach ($this->tables as $table) {
                    $centralTable = DB::connection('central')->table($table);
                    
                    // Check if table exists in dynamic tenant DB
                    if (!Schema::hasTable($table)) {
                        $this->warn("  ! Table '{$table}' does not exist in tenant database. Skipping.");
                        continue;
                    }

                    $records = $centralTable->where('school_id', $school->id)->get();
                    
                    if ($records->isNotEmpty()) {
                        $this->line("  - Migrating " . $records->count() . " records to '{$table}'...");
                        
                        foreach ($records as $record) {
                            // Use insertOrIgnore to avoid duplicates if re-run
                            DB::table($table)->insertOrIgnore((array) $record);
                        }
                    }
                }

                // Handle RBAC (Roles/Permissions) if they use teams
                // This part depends on how Spatie is configured, 
                // but since they were in central DB without school_id in my list, 
                // we might need to find them via users.
                // For now, I'll assume users' roles will be migrated if 'model_has_roles' is in my list.
                // Wait, 'model_has_roles' is NOT in my list because it usually doesn't have school_id.
                
                $this->migrateRbac($school);
            });

            $this->info("Migration completed for {$school->name}");
        }

        $this->info("--------------------------------------------------");
        $this->info("All schools migrated successfully.");
    }

    /**
     * Migrate RBAC data for the school's users.
     */
    protected function migrateRbac($school)
    {
        $userIds = DB::connection('central')->table('users')
            ->where('school_id', $school->id)
            ->pluck('id');

        if ($userIds->isEmpty()) return;

        $rbacTables = ['model_has_roles', 'model_has_permissions'];
        
        foreach ($rbacTables as $table) {
            $records = DB::connection('central')->table($table)
                ->where('model_type', 'App\Models\User')
                ->whereIn('model_id', $userIds)
                ->get();

            if ($records->isNotEmpty()) {
                $this->line("  - Migrating " . $records->count() . " RBAC records to '{$table}'...");
                foreach ($records as $record) {
                    DB::table($table)->insertOrIgnore((array) $record);
                }
            }
        }
    }
}
