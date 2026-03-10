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
        // Student Health Records
        Schema::create('student_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group', 10)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->text('vision_status')->nullable();
            $table->text('hearing_status')->nullable();
            $table->text('dental_status')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('dietary_restrictions')->nullable();
            $table->text('medications')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone')->nullable();
            $table->string('hospital_name')->nullable();
            $table->string('hospital_phone')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('special_instructions')->nullable();
            $table->date('last_physical_exam')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('student_id');
        });

        // Medical Conditions
        Schema::create('medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->text('symptoms')->nullable();
            $table->text('first_aid_instructions')->nullable();
            $table->boolean('is_chronic')->default(false);
            $table->boolean('requires_medication')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Student Medical Conditions (pivot)
        Schema::create('student_medical_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_condition_id')->constrained()->cascadeOnDelete();
            $table->date('diagnosed_date')->nullable();
            $table->string('severity', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'medical_condition_id']);
        });

        // Immunizations/Vaccinations
        Schema::create('immunization_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->integer('recommended_age_months')->nullable();
            $table->integer('doses_required')->default(1);
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student Immunizations
        Schema::create('student_immunizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('immunization_type_id')->constrained()->cascadeOnDelete();
            $table->integer('dose_number')->default(1);
            $table->date('administered_date');
            $table->string('administered_by')->nullable();
            $table->string('administered_at')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('next_dose_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('certificate_file')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'immunization_type_id']);
        });

        // Medical Visits (clinic visits)
        Schema::create('medical_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->datetime('visit_datetime');
            $table->string('visit_type', 100); // routine, emergency, illness, injury, follow_up
            $table->text('chief_complaint');
            $table->text('symptoms')->nullable();
            $table->text('vital_signs')->nullable(); // JSON: temperature, pulse, blood_pressure, etc.
            $table->text('examination_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_given')->nullable();
            $table->text('medications_prescribed')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('referred_to_hospital')->default(false);
            $table->string('referral_hospital')->nullable();
            $table->text('referral_reason')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->boolean('parent_pickup_required')->default(false);
            $table->timestamp('parent_pickup_at')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->foreignId('attended_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'visit_datetime']);
        });

        // Health Screenings
        Schema::create('health_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('screening_type', 100); // vision, hearing, dental, general, bmi
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('screening_date');
            $table->json('target_grades')->nullable();
            $table->string('conducted_by')->nullable();
            $table->string('organization')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->integer('students_screened')->default(0);
            $table->text('summary_report')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Student Screening Results
        Schema::create('screening_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('health_screening_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('findings')->nullable();
            $table->enum('result_status', ['normal', 'requires_attention', 'referred'])->default('normal');
            $table->text('recommendations')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['health_screening_id', 'student_id']);
        });

        // First Aid/Treatment Log
        Schema::create('first_aid_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->morphs('patient'); // student or teacher
            $table->datetime('incident_datetime');
            $table->string('incident_type', 100); // injury, illness, emergency
            $table->string('location')->nullable();
            $table->text('description');
            $table->text('treatment_provided');
            $table->text('medications_given')->nullable();
            $table->enum('severity', ['minor', 'moderate', 'severe'])->default('minor');
            $table->boolean('ambulance_called')->default(false);
            $table->boolean('hospitalized')->default(false);
            $table->boolean('parent_notified')->default(false);
            $table->text('follow_up_required')->nullable();
            $table->foreignId('treated_by')->constrained('users');
            $table->timestamps();
        });

        // Medication Administration
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency'); // daily, twice_daily, as_needed
            $table->text('instructions')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('prescribed_by')->nullable();
            $table->string('prescription_file')->nullable();
            $table->boolean('parent_consent')->default(false);
            $table->string('consent_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Medication Administration Log
        Schema::create('medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_schedule_id')->constrained()->cascadeOnDelete();
            $table->datetime('administered_at');
            $table->string('dosage_given');
            $table->text('notes')->nullable();
            $table->boolean('refused')->default(false);
            $table->string('refusal_reason')->nullable();
            $table->foreignId('administered_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_logs');
        Schema::dropIfExists('medication_schedules');
        Schema::dropIfExists('first_aid_logs');
        Schema::dropIfExists('screening_results');
        Schema::dropIfExists('health_screenings');
        Schema::dropIfExists('medical_visits');
        Schema::dropIfExists('student_immunizations');
        Schema::dropIfExists('immunization_types');
        Schema::dropIfExists('student_medical_conditions');
        Schema::dropIfExists('medical_conditions');
        Schema::dropIfExists('student_health_records');
    }
};
