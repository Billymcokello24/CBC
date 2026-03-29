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
        // Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->foreignId('head_of_department_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
        });

        // Grade Levels (PP1, PP2, Grade 1-9, etc.)
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Grade 1", "PP1"
            $table->string('code', 20); // e.g., "G1", "PP1"
            $table->integer('level_order'); // For sorting: 1, 2, 3...
            $table->string('category', 50); // pre_primary, lower_primary, upper_primary, junior_secondary
            $table->integer('minimum_age')->nullable();
            $table->integer('maximum_age')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'level_order']);
        });

        // Streams (e.g., East, West, Blue, Red)
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Classes (Combination of Grade Level + Stream)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stream_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Grade 4 East"
            $table->string('code', 30); // e.g., "G4E"
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assistant_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('room_number', 50)->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code', 'academic_year_id']);
            $table->index(['school_id', 'academic_year_id']);
        });

        // Sections (for large classes that need to be split)
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Section A"
            $table->string('code', 20);
            $table->integer('capacity')->nullable();
            $table->foreignId('section_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['class_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sections');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('streams');
        Schema::dropIfExists('grade_levels');
        Schema::dropIfExists('departments');
    }
};
