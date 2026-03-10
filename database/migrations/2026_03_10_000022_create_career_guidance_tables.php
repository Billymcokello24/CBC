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
        // Career Categories
        Schema::create('career_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career Pathways
        Schema::create('career_pathways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->text('overview')->nullable();
            $table->text('key_skills')->nullable();
            $table->text('education_requirements')->nullable();
            $table->text('certifications')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('job_outlook')->nullable();
            $table->text('related_subjects')->nullable();
            $table->text('work_environment')->nullable();
            $table->text('advancement_opportunities')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Career-Subject Mappings
        Schema::create('career_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_pathway_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->enum('importance', ['essential', 'recommended', 'helpful'])->default('helpful');
            $table->text('relevance_description')->nullable();
            $table->timestamps();

            $table->unique(['career_pathway_id', 'subject_id']);
        });

        // Career-Competency Mappings
        Schema::create('career_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_pathway_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->enum('importance', ['critical', 'important', 'useful'])->default('useful');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['career_pathway_id', 'competency_id']);
        });

        // Student Interest Assessments
        Schema::create('interest_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30);
            $table->text('description')->nullable();
            $table->string('assessment_type', 50); // holland, mbti, multiple_intelligence, custom
            $table->json('questions')->nullable();
            $table->json('scoring_rules')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Student Interest Results
        Schema::create('student_interest_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interest_assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->json('responses')->nullable();
            $table->json('scores')->nullable();
            $table->json('personality_type')->nullable();
            $table->json('top_interests')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
        });

        // Student Career Interests (manual tracking)
        Schema::create('student_career_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_pathway_id')->constrained()->cascadeOnDelete();
            $table->integer('interest_level')->default(3); // 1-5 scale
            $table->text('reason')->nullable();
            $table->date('expressed_date');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'career_pathway_id']);
        });

        // Career Recommendations
        Schema::create('career_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_pathway_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('match_score', 5, 2)->nullable();
            $table->enum('recommendation_type', ['system', 'teacher', 'counselor'])->default('system');
            $table->text('rationale')->nullable();
            $table->json('strength_areas')->nullable();
            $table->json('development_areas')->nullable();
            $table->text('action_items')->nullable();
            $table->foreignId('recommended_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->index(['student_id', 'academic_year_id']);
        });

        // Career Counseling Sessions
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_type', 100); // career, academic, personal, behavioral
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('objectives')->nullable();
            $table->text('discussion_notes')->nullable();
            $table->text('outcomes')->nullable();
            $table->text('action_plan')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('parent_notified')->default(false);
            $table->boolean('is_confidential')->default(true);
            $table->timestamps();

            $table->index(['student_id', 'scheduled_at']);
        });

        // Mentorship Programs
        Schema::create('mentorship_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('program_type', 100); // peer, teacher, external
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('max_mentees_per_mentor')->default(5);
            $table->boolean('is_active')->default(true);
            $table->foreignId('coordinator_id')->constrained('users');
            $table->timestamps();
        });

        // Mentor-Mentee Pairs
        Schema::create('mentorships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('mentorship_programs')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentee_id')->constrained('students')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('focus_area', 100)->nullable();
            $table->text('goals')->nullable();
            $table->enum('status', ['active', 'completed', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'mentee_id']);
        });

        // Career Resources
        Schema::create('career_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('career_pathway_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_type', 50); // article, video, website, document, tool
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->string('source')->nullable();
            $table->json('target_grades')->nullable();
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Work Experience / Internships Tracking
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_pathway_id')->nullable()->constrained()->nullOnDelete();
            $table->string('organization_name');
            $table->string('position');
            $table->string('experience_type', 50); // internship, job_shadow, volunteer, apprenticeship
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('hours_completed')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_contact')->nullable();
            $table->text('responsibilities')->nullable();
            $table->text('skills_gained')->nullable();
            $table->text('reflection')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('supervisor_feedback')->nullable();
            $table->string('certificate_file')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
        Schema::dropIfExists('career_resources');
        Schema::dropIfExists('mentorships');
        Schema::dropIfExists('mentorship_programs');
        Schema::dropIfExists('counseling_sessions');
        Schema::dropIfExists('career_recommendations');
        Schema::dropIfExists('student_career_interests');
        Schema::dropIfExists('student_interest_results');
        Schema::dropIfExists('interest_assessments');
        Schema::dropIfExists('career_competencies');
        Schema::dropIfExists('career_subjects');
        Schema::dropIfExists('career_pathways');
        Schema::dropIfExists('career_categories');
    }
};
