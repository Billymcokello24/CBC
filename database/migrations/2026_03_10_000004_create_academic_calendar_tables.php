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
        // Academic Years
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "2026 Academic Year"
            $table->string('code', 20); // e.g., "2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->enum('status', ['upcoming', 'active', 'completed', 'archived'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'is_current']);
        });

        // Academic Terms/Semesters
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., "Term 1", "Semester 1"
            $table->integer('term_number'); // 1, 2, 3
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_weeks')->nullable();
            $table->boolean('is_current')->default(false);
            $table->enum('status', ['upcoming', 'active', 'completed', 'archived'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['academic_year_id', 'term_number']);
            $table->index(['school_id', 'is_current']);
        });

        // School Holidays/Breaks
        Schema::create('school_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('holiday_type', 50); // public_holiday, school_break, exam_break, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_recurring')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'start_date', 'end_date']);
        });

        // School Calendar Events
        Schema::create('school_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type', 100); // exam, meeting, activity, deadline, etc.
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->boolean('is_public')->default(true);
            $table->string('color', 20)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'event_date']);
            $table->index(['school_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_calendar_events');
        Schema::dropIfExists('school_holidays');
        Schema::dropIfExists('academic_terms');
        Schema::dropIfExists('academic_years');
    }
};
