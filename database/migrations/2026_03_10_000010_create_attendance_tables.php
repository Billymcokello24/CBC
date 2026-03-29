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
        // Student Attendance
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'half_day', 'holiday']);
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->string('absence_reason')->nullable();
            $table->string('late_reason')->nullable();
            $table->boolean('is_excused')->default(false);
            $table->string('excused_by')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('marked_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date']);
            $table->index(['class_id', 'attendance_date', 'status']);
            $table->index(['student_id', 'status', 'attendance_date']);
        });

        // Attendance Session (for marking attendance by period/subject)
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->integer('period_number')->nullable();
            $table->enum('session_type', ['morning', 'afternoon', 'period', 'full_day'])->default('full_day');
            $table->integer('students_present')->default(0);
            $table->integer('students_absent')->default(0);
            $table->integer('students_late')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'session_date']);
        });

        // Period-wise Attendance
        Schema::create('period_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused']);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['attendance_session_id', 'student_id']);
        });

        // Attendance Corrections/Amendments
        Schema::create('attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_attendance_id')->constrained('student_attendance')->cascadeOnDelete();
            $table->string('previous_status', 50);
            $table->string('new_status', 50);
            $table->text('reason');
            $table->foreignId('corrected_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Attendance Summary (aggregated data)
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->integer('total_school_days')->default(0);
            $table->integer('days_present')->default(0);
            $table->integer('days_absent')->default(0);
            $table->integer('days_late')->default(0);
            $table->integer('days_excused')->default(0);
            $table->decimal('attendance_percentage', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'academic_term_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
        Schema::dropIfExists('attendance_corrections');
        Schema::dropIfExists('period_attendance');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('student_attendance');
    }
};
