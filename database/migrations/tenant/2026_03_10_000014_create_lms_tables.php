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
        // Lesson Plans
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_strand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('week_number', 20)->nullable();
            $table->date('lesson_date');
            $table->integer('period_number')->nullable();
            $table->integer('duration_minutes')->default(35);
            $table->text('specific_objectives')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->text('key_vocabulary')->nullable();
            $table->text('teaching_aids')->nullable();
            $table->text('references')->nullable();
            $table->text('introduction')->nullable();
            $table->text('lesson_development')->nullable();
            $table->text('teacher_activities')->nullable();
            $table->text('learner_activities')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('assessment_methods')->nullable();
            $table->text('reflection')->nullable();
            $table->text('homework')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('feedback')->nullable();
            $table->boolean('is_taught')->default(false);
            $table->timestamp('taught_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['teacher_id', 'lesson_date']);
            $table->index(['class_id', 'subject_id', 'lesson_date']);
        });

        // Lesson Plan Competencies
        Schema::create('lesson_plan_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['lesson_plan_id', 'competency_id']);
        });

        // Schemes of Work
        Schema::create('schemes_of_work', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('total_weeks')->nullable();
            $table->integer('lessons_per_week')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Scheme of Work Entries
        Schema::create('scheme_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('schemes_of_work')->cascadeOnDelete();
            $table->integer('week_number');
            $table->integer('lesson_number');
            $table->foreignId('strand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_strand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('topic');
            $table->text('learning_outcomes')->nullable();
            $table->text('learning_activities')->nullable();
            $table->text('resources')->nullable();
            $table->text('assessment')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['scheme_id', 'week_number']);
        });

        // Courses (for LMS)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_level_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('duration_hours')->nullable();
            $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_self_paced')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_enrollments')->nullable();
            $table->boolean('require_approval')->default(false);
            $table->foreignId('instructor_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'is_published']);
        });

        // Course Modules
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('requires_completion')->default(false);
            $table->timestamps();
        });

        // Lessons (for LMS)
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['video', 'document', 'quiz', 'assignment', 'text', 'interactive']);
            $table->longText('content')->nullable();
            $table->string('video_url')->nullable();
            $table->string('document_path')->nullable();
            $table->integer('order_index')->default(0);
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_preview')->default(false); // Free preview content
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });

        // Lesson Resources
        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('resource_type', 50); // document, video, audio, link, file
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_downloadable')->default(true);
            $table->timestamps();
        });

        // Course Enrollments
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'dropped', 'expired'])->default('pending');
            $table->integer('progress_percentage')->default(0);
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->text('certificate_url')->nullable();
            $table->timestamp('certificate_issued_at')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        // Lesson Progress
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('completion_percentage')->default(0);
            $table->string('last_position')->nullable(); // for videos, bookmarks
            $table->timestamps();

            $table->unique(['enrollment_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('lesson_resources');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('scheme_entries');
        Schema::dropIfExists('schemes_of_work');
        Schema::dropIfExists('lesson_plan_competencies');
        Schema::dropIfExists('lesson_plans');
    }
};
