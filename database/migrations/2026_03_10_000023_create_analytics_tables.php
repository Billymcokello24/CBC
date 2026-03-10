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
        // Report Types
        Schema::create('report_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('category', 100); // academic, financial, administrative, compliance
            $table->text('description')->nullable();
            $table->string('template_file')->nullable();
            $table->json('parameters')->nullable();
            $table->json('filters')->nullable();
            $table->boolean('is_scheduled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Metric Definitions
        Schema::create('metric_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('category', 100); // academic, attendance, behavior, financial
            $table->text('description')->nullable();
            $table->string('calculation_method', 100)->nullable();
            $table->text('formula')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('warning_threshold', 10, 2)->nullable();
            $table->decimal('critical_threshold', 10, 2)->nullable();
            $table->boolean('higher_is_better')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Performance Metrics (actual values)
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metric_definition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('measurable'); // class, grade_level, subject, teacher, etc.
            $table->date('measurement_date');
            $table->decimal('value', 15, 4);
            $table->decimal('previous_value', 15, 4)->nullable();
            $table->decimal('change_percentage', 8, 2)->nullable();
            $table->string('trend', 20)->nullable(); // up, down, stable
            $table->json('breakdown')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'metric_definition_id', 'measurement_date'], 'perf_metric_date_idx');
        });

        // Dashboard Widgets
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('widget_type', 50); // chart, counter, table, gauge, progress
            $table->string('chart_type', 50)->nullable(); // bar, line, pie, area, etc.
            $table->text('description')->nullable();
            $table->json('data_source')->nullable();
            $table->json('default_config')->nullable();
            $table->json('available_filters')->nullable();
            $table->string('icon')->nullable();
            $table->json('roles_allowed')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // User Dashboard Configurations
        Schema::create('user_dashboard_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('dashboard_type', 50)->default('main'); // main, academic, financial
            $table->json('layout')->nullable();
            $table->json('widget_settings')->nullable();
            $table->string('theme', 50)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'dashboard_type']);
        });

        // Analytics Reports (generated reports)
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->date('report_date');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->json('parameters')->nullable();
            $table->json('filters_applied')->nullable();
            $table->longText('data')->nullable();
            $table->text('summary')->nullable();
            $table->json('key_findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_format', 20)->nullable();
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'report_type_id', 'report_date']);
        });

        // Report Schedules
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_type_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('frequency', 50); // daily, weekly, monthly, termly, yearly
            $table->string('day_of_week', 20)->nullable();
            $table->integer('day_of_month')->nullable();
            $table->time('time_of_day')->nullable();
            $table->json('parameters')->nullable();
            $table->json('recipients')->nullable();
            $table->boolean('send_email')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Data Snapshots (historical data preservation)
        Schema::create('data_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('snapshot_type', 100); // enrollment, academic_results, attendance, financial
            $table->date('snapshot_date');
            $table->longText('data');
            $table->json('summary_stats')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['school_id', 'snapshot_type', 'snapshot_date']);
        });

        // Trend Analysis
        Schema::create('trend_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metric_definition_id')->constrained()->cascadeOnDelete();
            $table->string('analysis_period', 50); // weekly, monthly, termly, yearly
            $table->date('start_date');
            $table->date('end_date');
            $table->json('data_points')->nullable();
            $table->string('trend_direction', 20)->nullable();
            $table->decimal('trend_strength', 5, 2)->nullable();
            $table->decimal('average_value', 15, 4)->nullable();
            $table->decimal('min_value', 15, 4)->nullable();
            $table->decimal('max_value', 15, 4)->nullable();
            $table->decimal('standard_deviation', 15, 4)->nullable();
            $table->json('forecast')->nullable();
            $table->text('insights')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
        });

        // Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
        });

        // System Logs
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20); // emergency, alert, critical, error, warning, notice, info, debug
            $table->string('channel', 100);
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->index(['level', 'logged_at']);
            $table->index('channel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('trend_analyses');
        Schema::dropIfExists('data_snapshots');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('analytics_reports');
        Schema::dropIfExists('user_dashboard_configs');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('performance_metrics');
        Schema::dropIfExists('metric_definitions');
        Schema::dropIfExists('report_types');
    }
};
