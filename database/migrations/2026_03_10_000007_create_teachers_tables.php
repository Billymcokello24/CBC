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
        // Staff Categories
        Schema::create('staff_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Teaching Staff, Non-Teaching Staff, Administrative
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Staff Designations
        Schema::create('staff_designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_category_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Principal, Deputy Principal, HOD, Teacher, etc.
            $table->string('code', 20);
            $table->integer('hierarchy_level')->default(0); // For organizational chart
            $table->text('description')->nullable();
            $table->text('responsibilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Teachers/Staff
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_designation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('staff_number')->nullable();
            $table->string('tsc_number', 50)->nullable(); // Teachers Service Commission number
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->string('id_number', 50)->nullable();
            $table->string('nationality', 100)->default('Kenyan');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('county', 100)->nullable();
            $table->string('sub_county', 100)->nullable();
            $table->string('photo')->nullable();
            $table->date('date_joined');
            $table->date('date_left')->nullable();
            $table->string('contract_type', 50)->nullable(); // permanent, contract, intern
            $table->string('employment_type', 50)->default('full_time'); // full_time, part_time
            $table->string('marital_status', 50)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->text('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->string('nhif_number', 50)->nullable();
            $table->string('nssf_number', 50)->nullable();
            $table->string('kra_pin', 50)->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated', 'resigned', 'retired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'staff_number']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'department_id']);
            $table->index('tsc_number');
        });

        // Teacher Qualifications
        Schema::create('teacher_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('qualification_type', 100); // certificate, diploma, degree, masters, phd
            $table->string('qualification_name');
            $table->string('institution');
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('grade_obtained', 50)->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('certificate_file')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index('teacher_id');
        });

        // Teacher Certifications
        Schema::create('teacher_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('certification_name');
            $table->string('issuing_organization');
            $table->string('certification_number')->nullable();
            $table->date('issue_date');
            $table->date('expiry_date')->nullable();
            $table->string('certificate_file')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        // Teacher Documents
        Schema::create('teacher_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 100); // id_copy, cv, appointment_letter, etc.
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Annual Leave, Sick Leave, Maternity, etc.
            $table->string('code', 20);
            $table->integer('default_days')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('requires_documentation')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Teacher Leaves
        Schema::create('teacher_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days');
            $table->text('reason');
            $table->string('supporting_document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Teacher Attendance
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave', 'holiday']);
            $table->string('late_reason')->nullable();
            $table->string('absence_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['teacher_id', 'attendance_date']);
            $table->index(['school_id', 'attendance_date']);
        });

        // Teacher Appraisals
        Schema::create('teacher_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('appraisal_type', 100); // annual, mid_year, probation
            $table->date('appraisal_date');
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('rating', 50)->nullable(); // excellent, good, satisfactory, needs_improvement
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('goals_set')->nullable();
            $table->text('comments')->nullable();
            $table->text('employee_comments')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'completed'])->default('draft');
            $table->foreignId('appraised_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'academic_year_id']);
        });

        // Teacher Training
        Schema::create('teacher_trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('training_name');
            $table->string('training_type', 100); // workshop, seminar, course, conference
            $table->string('organizer')->nullable();
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->text('description')->nullable();
            $table->text('skills_acquired')->nullable();
            $table->string('certificate_file')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->boolean('school_sponsored')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_trainings');
        Schema::dropIfExists('teacher_appraisals');
        Schema::dropIfExists('teacher_attendance');
        Schema::dropIfExists('teacher_leaves');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('teacher_documents');
        Schema::dropIfExists('teacher_certifications');
        Schema::dropIfExists('teacher_qualifications');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('staff_designations');
        Schema::dropIfExists('staff_categories');
    }
};
