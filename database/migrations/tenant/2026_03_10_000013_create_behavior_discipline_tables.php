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
        // Behavior Categories
        Schema::create('behavior_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['positive', 'negative']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Behavior Types
        Schema::create('behavior_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('behavior_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->integer('default_points')->default(0); // positive or negative points
            $table->integer('severity_level')->default(1); // 1-5 scale
            $table->boolean('requires_parent_notification')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Behavior Records
        Schema::create('behavior_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('behavior_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location')->nullable();
            $table->text('description');
            $table->text('witnesses')->nullable();
            $table->integer('points')->default(0);
            $table->boolean('is_resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->date('resolution_date')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'incident_date']);
            $table->index(['class_id', 'incident_date']);
        });

        // Disciplinary Actions
        Schema::create('disciplinary_action_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->integer('severity_level')->default(1);
            $table->integer('default_duration_days')->nullable();
            $table->boolean('requires_parent_meeting')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Disciplinary Actions
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('behavior_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('action_type_id')->constrained('disciplinary_action_types')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'appealed', 'revoked'])->default('pending');
            $table->text('conditions')->nullable();
            $table->boolean('parent_meeting_required')->default(false);
            $table->date('parent_meeting_date')->nullable();
            $table->text('parent_meeting_notes')->nullable();
            $table->foreignId('issued_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('appeal_notes')->nullable();
            $table->timestamps();
        });

        // Behavior Interventions
        Schema::create('behavior_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('behavior_record_id')->nullable()->constrained()->nullOnDelete();
            $table->string('intervention_type', 100); // counseling, mentoring, peer_support, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->text('progress_notes')->nullable();
            $table->text('outcome')->nullable();
            $table->boolean('was_successful')->nullable();
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        // Behavior Goals
        Schema::create('behavior_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->string('goal');
            $table->text('description')->nullable();
            $table->text('success_criteria')->nullable();
            $table->date('target_date')->nullable();
            $table->enum('status', ['active', 'achieved', 'not_achieved', 'revised'])->default('active');
            $table->integer('progress_percentage')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Incident Reports (for serious incidents)
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('incident_number')->unique();
            $table->string('title');
            $table->enum('incident_type', ['accident', 'injury', 'fight', 'bullying', 'theft', 'vandalism', 'misconduct', 'emergency', 'other']);
            $table->date('incident_date');
            $table->time('incident_time');
            $table->string('location');
            $table->text('description');
            $table->text('immediate_action')->nullable();
            $table->json('students_involved')->nullable();
            $table->json('staff_involved')->nullable();
            $table->json('witnesses')->nullable();
            $table->text('evidence_collected')->nullable();
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->default('minor');
            $table->enum('status', ['reported', 'investigating', 'resolved', 'closed'])->default('reported');
            $table->text('investigation_notes')->nullable();
            $table->text('resolution')->nullable();
            $table->text('preventive_measures')->nullable();
            $table->boolean('parent_notification_required')->default(false);
            $table->boolean('parents_notified')->default(false);
            $table->boolean('authority_notification_required')->default(false);
            $table->boolean('authorities_notified')->default(false);
            $table->foreignId('reported_by')->constrained('users');
            $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'incident_date']);
            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('behavior_goals');
        Schema::dropIfExists('behavior_interventions');
        Schema::dropIfExists('disciplinary_actions');
        Schema::dropIfExists('disciplinary_action_types');
        Schema::dropIfExists('behavior_records');
        Schema::dropIfExists('behavior_types');
        Schema::dropIfExists('behavior_categories');
    }
};
