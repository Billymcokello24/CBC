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
        // Assignments
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete(); // LMS lesson link
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->enum('assignment_type', ['homework', 'project', 'practical', 'research', 'presentation', 'group_work']);
            $table->date('assigned_date');
            $table->datetime('due_date');
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('passing_marks', 8, 2)->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->datetime('late_submission_deadline')->nullable();
            $table->decimal('late_penalty_percentage', 5, 2)->default(0);
            $table->integer('max_attempts')->default(1);
            $table->boolean('is_group_assignment')->default(false);
            $table->integer('group_size_min')->nullable();
            $table->integer('group_size_max')->nullable();
            $table->boolean('is_published')->default(false);
            $table->enum('status', ['draft', 'published', 'closed', 'graded'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'subject_id', 'status']);
            $table->index(['teacher_id', 'due_date']);
        });

        // Assignment Attachments
        Schema::create('assignment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->integer('file_size');
            $table->timestamps();
        });

        // Assignment-Learning Outcome Links
        Schema::create('assignment_learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['assignment_id', 'learning_outcome_id'], 'assign_lo_unique');
        });

        // Assignment Submissions
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable(); // for group assignments
            $table->text('content')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->timestamp('submitted_at');
            $table->boolean('is_late')->default(false);
            $table->enum('status', ['submitted', 'resubmitted', 'grading', 'graded', 'returned'])->default('submitted');
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('penalty_applied', 8, 2)->nullable();
            $table->decimal('final_marks', 8, 2)->nullable();
            $table->string('grade', 20)->nullable();
            $table->text('feedback')->nullable();
            $table->text('private_notes')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'assign_stud_attempt_unique');
            $table->index(['student_id', 'status']);
        });

        // Submission Attachments
        Schema::create('submission_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type', 50);
            $table->integer('file_size');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        // Project Groups (for group assignments)
        Schema::create('project_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('students')->nullOnDelete();
            $table->integer('max_members')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        // Project Group Members
        Schema::create('project_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('project_groups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('role', 100)->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->unique(['group_id', 'student_id']);
        });

        // Peer Reviews
        Schema::create('peer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('assignment_submissions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('students')->cascadeOnDelete();
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('feedback')->nullable();
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->unique(['submission_id', 'reviewer_id']);
        });

        // Portfolios
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('portfolio_type', 100)->default('academic'); // academic, talent, comprehensive
            $table->boolean('is_public')->default(false);
            $table->string('visibility', 50)->default('private'); // private, school, public
            $table->string('theme', 50)->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'portfolio_type']);
        });

        // Portfolio Categories
        Schema::create('portfolio_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Portfolio Items
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('portfolio_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('item_type', ['text', 'image', 'video', 'document', 'link', 'project', 'certificate', 'award']);
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->date('item_date')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();
            $table->text('reflection')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['portfolio_id', 'item_type']);
        });

        // Portfolio Item Competencies
        Schema::create('portfolio_item_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['portfolio_item_id', 'competency_id'], 'port_item_comp_unique');
        });

        // Talent Categories
        Schema::create('talent_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Student Talents
        Schema::create('student_talents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_category_id')->constrained()->cascadeOnDelete();
            $table->string('talent_name');
            $table->text('description')->nullable();
            $table->enum('proficiency_level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->date('identified_date')->nullable();
            $table->text('evidence')->nullable();
            $table->foreignId('identified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'talent_category_id']);
        });

        // Talent Assessments
        Schema::create('talent_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_talent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->date('assessment_date');
            $table->string('previous_level', 50)->nullable();
            $table->string('current_level', 50);
            $table->text('observations')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('development_plan')->nullable();
            $table->foreignId('assessed_by')->constrained('users');
            $table->timestamps();
        });

        // Student Achievements
        Schema::create('student_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('achievement_type', 100); // academic, sports, arts, leadership, community, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('level', 50)->nullable(); // school, county, national, international
            $table->string('position', 50)->nullable();
            $table->date('achievement_date');
            $table->string('certificate_file')->nullable();
            $table->string('photo')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'achievement_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_achievements');
        Schema::dropIfExists('talent_assessments');
        Schema::dropIfExists('student_talents');
        Schema::dropIfExists('talent_categories');
        Schema::dropIfExists('portfolio_item_competencies');
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('portfolio_categories');
        Schema::dropIfExists('portfolios');
        Schema::dropIfExists('peer_reviews');
        Schema::dropIfExists('project_group_members');
        Schema::dropIfExists('project_groups');
        Schema::dropIfExists('submission_attachments');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignment_learning_outcomes');
        Schema::dropIfExists('assignment_attachments');
        Schema::dropIfExists('assignments');
    }
};
