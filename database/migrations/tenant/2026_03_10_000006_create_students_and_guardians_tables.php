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
        // Parents/Guardians
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('id_number', 50)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            $table->text('work_address')->nullable();
            $table->text('home_address')->nullable();
            $table->string('county', 100)->nullable();
            $table->string('sub_county', 100)->nullable();
            $table->enum('relationship_type', ['father', 'mother', 'guardian', 'other'])->default('guardian');
            $table->string('photo')->nullable();
            $table->boolean('is_emergency_contact')->default(true);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('receives_communication')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'phone']);
            $table->index(['school_id', 'email']);
        });

        // Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('admission_number')->nullable();
            $table->string('upi', 50)->nullable(); // Unique Personal Identifier (government)
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth');
            $table->string('birth_certificate_number', 50)->nullable();
            $table->string('nationality', 100)->default('Kenyan');
            $table->string('religion', 100)->nullable();
            $table->text('home_address')->nullable();
            $table->string('county', 100)->nullable();
            $table->string('sub_county', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->string('photo')->nullable();
            $table->date('admission_date');
            $table->foreignId('admission_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('current_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('blood_group', 10)->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->boolean('has_special_needs')->default(false);
            $table->text('special_needs_details')->nullable();
            $table->string('primary_language', 100)->nullable();
            $table->string('secondary_language', 100)->nullable();
            $table->enum('boarding_status', ['day', 'boarding'])->default('day');
            $table->string('hostel_room', 50)->nullable();
            $table->string('transport_route')->nullable();
            $table->string('pickup_point')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'transferred', 'withdrawn', 'suspended'])->default('active');
            $table->date('graduation_date')->nullable();
            $table->date('withdrawal_date')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'admission_number']);
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'current_class_id']);
            $table->index('upi');
        });

        // Student-Guardian Relationship (Pivot)
        Schema::create('student_guardian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'guardian', 'uncle', 'aunt', 'grandparent', 'sibling', 'other']);
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->boolean('receives_reports')->default(true);
            $table->boolean('receives_fees_notification')->default(true);
            $table->boolean('is_fee_payer')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'guardian_id']);
        });

        // Student Enrollments (tracking class enrollment history)
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->date('enrollment_date');
            $table->date('end_date')->nullable();
            $table->enum('enrollment_type', ['new', 'promoted', 'repeated', 'transferred_in', 'rejoined']);
            $table->enum('status', ['active', 'completed', 'transferred', 'withdrawn', 'promoted', 'repeated']);
            $table->string('roll_number', 20)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'class_id', 'academic_year_id']);
            $table->index(['class_id', 'academic_year_id', 'status']);
        });

        // Student Documents
        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 100); // birth_certificate, medical_report, transfer_letter, etc.
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

            $table->index(['student_id', 'document_type']);
        });

        // Student Siblings (tracking siblings in same school)
        Schema::create('student_siblings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sibling_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship', 50); // brother, sister, twin, etc.
            $table->timestamps();

            $table->unique(['student_id', 'sibling_id']);
        });

        // Previous Schools (for transfer students)
        Schema::create('student_previous_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('school_name');
            $table->string('school_address')->nullable();
            $table->string('school_phone')->nullable();
            $table->string('last_class_attended')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->string('reason_for_leaving')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('has_transfer_certificate')->default(false);
            $table->timestamps();
        });

        // Student Status History
        Schema::create('student_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 50);
            $table->string('new_status', 50);
            $table->text('reason')->nullable();
            $table->date('effective_date');
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_status_history');
        Schema::dropIfExists('student_previous_schools');
        Schema::dropIfExists('student_siblings');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('student_enrollments');
        Schema::dropIfExists('student_guardian');
        Schema::dropIfExists('students');
        Schema::dropIfExists('guardians');
    }
};
