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
        // Assessment Types
        Schema::create('assessment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Formative, Summative, Project, Practical, Oral, etc.
            $table->string('code', 20);
            $table->string('category', 50); // formative, summative
            $table->text('description')->nullable();
            $table->decimal('default_weight', 5, 2)->default(0); // percentage weight
            $table->boolean('requires_rubric')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Grading Scales
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('scale_type', 50); // cbc_levels, percentage, letter, numeric
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Grade Descriptors (levels within a grading scale)
        Schema::create('grade_descriptors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grading_scale_id')->constrained()->cascadeOnDelete();
            $table->string('level_code', 10); // EE, ME, AE, BE for CBC
            $table->string('level_name'); // Exceeding Expectation, Meeting Expectation, etc.
            $table->text('description')->nullable();
            $table->decimal('min_score', 5, 2)->nullable();
            $table->decimal('max_score', 5, 2)->nullable();
            $table->integer('points')->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('grading_scale_id');
        });

        // Assessments
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grading_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->date('assessment_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('passing_marks', 8, 2)->nullable();
            $table->decimal('weight', 5, 2)->default(100); // weight percentage
            $table->boolean('is_published')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->enum('status', ['draft', 'scheduled', 'in_progress', 'completed', 'graded', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'subject_id', 'academic_term_id']);
            $table->index(['teacher_id', 'status']);
        });

        // Linking Assessments to Strands/Sub-Strands
        Schema::create('assessment_strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('sub_strand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Linking Assessments to Learning Outcomes
        Schema::create('assessment_learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['assessment_id', 'learning_outcome_id'], 'assess_lo_unique');
        });

        // Linking Assessments to Competencies
        Schema::create('assessment_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['assessment_id', 'competency_id']);
        });

        // Rubrics
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_type_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('total_points', 8, 2)->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Rubric Criteria
        Schema::create('rubric_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $table->string('criterion_name');
            $table->text('description')->nullable();
            $table->decimal('max_points', 8, 2);
            $table->decimal('weight', 5, 2)->default(100);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Rubric Levels (performance levels for each criterion)
        Schema::create('rubric_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_criteria_id')->constrained('rubric_criteria')->cascadeOnDelete();
            $table->string('level_name'); // Excellent, Good, Satisfactory, Needs Improvement
            $table->text('description')->nullable();
            $table->decimal('points', 8, 2);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Assessment Rubric Assignment
        Schema::create('assessment_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['assessment_id', 'rubric_id']);
        });

        // Student Assessment Results
        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->foreignId('grade_descriptor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('grade_level', 20)->nullable(); // EE, ME, AE, BE
            $table->text('feedback')->nullable();
            $table->text('teacher_comments')->nullable();
            $table->text('improvement_areas')->nullable();
            $table->boolean('is_absent')->default(false);
            $table->string('absence_reason')->nullable();
            $table->boolean('is_excused')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'assessment_id']);
            $table->index(['assessment_id', 'grade_level']);
        });

        // Student Assessment Rubric Scores
        Schema::create('student_rubric_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_assessment_id')->constrained('student_assessments')->cascadeOnDelete();
            $table->foreignId('rubric_criteria_id')->constrained('rubric_criteria')->cascadeOnDelete();
            $table->foreignId('rubric_level_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('score', 8, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['student_assessment_id', 'rubric_criteria_id'], 'stud_assess_rubric_unique');
        });

        // Student Competency Ratings
        Schema::create('student_competency_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rating_level', 20); // EE, ME, AE, BE
            $table->decimal('score', 5, 2)->nullable();
            $table->text('evidence')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('rated_by')->constrained('users');
            $table->timestamp('rated_at');
            $table->timestamps();

            $table->index(['student_id', 'competency_id', 'academic_term_id'], 'stud_comp_term_idx');
        });

        // Report Cards
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
            $table->integer('total_subjects')->default(0);
            $table->decimal('average_score', 5, 2)->nullable();
            $table->string('overall_grade', 20)->nullable();
            $table->integer('class_rank')->nullable();
            $table->integer('stream_rank')->nullable();
            $table->integer('total_students_in_class')->nullable();
            $table->text('class_teacher_comments')->nullable();
            $table->text('principal_comments')->nullable();
            $table->text('parent_comments')->nullable();
            $table->integer('days_present')->nullable();
            $table->integer('days_absent')->nullable();
            $table->integer('total_school_days')->nullable();
            $table->string('conduct_grade', 20)->nullable();
            $table->string('effort_grade', 20)->nullable();
            $table->enum('status', ['draft', 'generated', 'approved', 'published'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['student_id', 'academic_term_id']);
            $table->index(['class_id', 'academic_term_id']);
        });

        // Report Card Subject Results
        Schema::create('report_card_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('marks_obtained', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade', 20)->nullable();
            $table->integer('subject_rank')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->timestamps();

            $table->unique(['report_card_id', 'subject_id']);
        });

        // Report Card Competency Summary
        Schema::create('report_card_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->string('rating', 20);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['report_card_id', 'competency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_competencies');
        Schema::dropIfExists('report_card_subjects');
        Schema::dropIfExists('report_cards');
        Schema::dropIfExists('student_competency_ratings');
        Schema::dropIfExists('student_rubric_scores');
        Schema::dropIfExists('student_assessments');
        Schema::dropIfExists('assessment_rubrics');
        Schema::dropIfExists('rubric_levels');
        Schema::dropIfExists('rubric_criteria');
        Schema::dropIfExists('rubrics');
        Schema::dropIfExists('assessment_competencies');
        Schema::dropIfExists('assessment_learning_outcomes');
        Schema::dropIfExists('assessment_strands');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('grade_descriptors');
        Schema::dropIfExists('grading_scales');
        Schema::dropIfExists('assessment_types');
    }
};
