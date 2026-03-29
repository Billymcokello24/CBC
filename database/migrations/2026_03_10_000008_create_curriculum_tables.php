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
        // Learning Areas (CBC main subject areas)
        Schema::create('learning_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Mathematics", "Languages", "Science & Technology"
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable(); // core, optional, co-curricular
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_area_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "English", "Kiswahili", "Mathematics"
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('subject_type', 50)->default('core'); // core, optional, elective
            $table->boolean('is_examinable')->default(true);
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Subject-Grade Level Mapping
        Schema::create('subject_grade_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->integer('lessons_per_week')->default(1);
            $table->integer('minutes_per_lesson')->default(35);
            $table->boolean('is_compulsory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['subject_id', 'grade_level_id']);
        });

        // Strands (Major topic areas within a subject)
        Schema::create('strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30);
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['subject_id', 'grade_level_id', 'code']);
        });

        // Sub-Strands (Specific topics within strands)
        Schema::create('sub_strands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strand_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 30);
            $table->text('description')->nullable();
            $table->integer('suggested_lessons')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['strand_id', 'code']);
        });

        // Competencies (CBC core competencies)
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Communication, Collaboration, Critical Thinking, etc.
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable(); // core, key
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Competency Indicators (measurable outcomes for competencies)
        Schema::create('competency_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->string('indicator');
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Learning Outcomes (specific outcomes for sub-strands)
        Schema::create('learning_outcomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_strand_id')->constrained()->cascadeOnDelete();
            $table->string('outcome');
            $table->string('code', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('outcome_type', 50)->nullable(); // knowledge, skill, attitude
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Linking Learning Outcomes to Competencies
        Schema::create('learning_outcome_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['learning_outcome_id', 'competency_id'], 'lo_competency_unique');
        });

        // Learning Indicators (CBC specific assessment indicators)
        Schema::create('learning_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_outcome_id')->constrained()->cascadeOnDelete();
            $table->string('indicator');
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Curriculum Resources
        Schema::create('curriculum_resources', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('resourceable'); // can link to subject, strand, sub_strand, etc.
            $table->string('title');
            $table->string('resource_type', 100); // textbook, video, audio, document, link
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn', 50)->nullable();
            $table->integer('year_published')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // School Subjects (school-specific subject configurations)
        Schema::create('school_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('local_code', 20)->nullable();
            $table->boolean('is_offered')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'subject_id']);
        });

        // Teacher Subject Assignments
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary_teacher')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['teacher_id', 'subject_id', 'class_id', 'academic_year_id'], 'teacher_subject_class_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
        Schema::dropIfExists('school_subjects');
        Schema::dropIfExists('curriculum_resources');
        Schema::dropIfExists('learning_indicators');
        Schema::dropIfExists('learning_outcome_competencies');
        Schema::dropIfExists('learning_outcomes');
        Schema::dropIfExists('competency_indicators');
        Schema::dropIfExists('competencies');
        Schema::dropIfExists('sub_strands');
        Schema::dropIfExists('strands');
        Schema::dropIfExists('subject_grade_levels');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('learning_areas');
    }
};
