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
        // Period Definitions
        Schema::create('period_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Period 1, Break, Lunch, etc.
            $table->integer('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration_minutes');
            $table->enum('period_type', ['lesson', 'break', 'lunch', 'assembly', 'other']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'period_number']);
        });

        // Timetable Templates
        Schema::create('timetable_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template_type', 50)->default('weekly'); // weekly, bi-weekly
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Timetables
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('timetable_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_published')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['class_id', 'academic_year_id', 'status']);
        });

        // Timetable Slots
        Schema::create('timetable_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('period_definition_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->integer('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number', 50)->nullable();
            $table->string('activity_type', 100)->nullable(); // lesson, lab, library, sports, etc.
            $table->text('notes')->nullable();
            $table->boolean('is_double_period')->default(false);
            $table->timestamps();

            $table->unique(['timetable_id', 'day_of_week', 'period_number']);
            $table->index(['teacher_id', 'day_of_week', 'period_number']);
        });

        // Room Management
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('room_number', 50);
            $table->string('room_type', 100); // classroom, laboratory, library, hall, staff_room, etc.
            $table->string('building', 100)->nullable();
            $table->integer('floor')->nullable();
            $table->integer('capacity')->nullable();
            $table->text('facilities')->nullable();
            $table->boolean('has_projector')->default(false);
            $table->boolean('has_computer')->default(false);
            $table->boolean('has_smartboard')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'room_number']);
        });

        // Room Bookings
        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booked_by')->constrained('users');
            $table->string('purpose');
            $table->date('booking_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('attendees_count')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'booking_date', 'status']);
        });

        // Teacher Substitutions
        Schema::create('substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('substitute_teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('timetable_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->date('substitution_date');
            $table->integer('period_number');
            $table->string('reason')->nullable();
            $table->text('lesson_notes')->nullable();
            $table->text('substitute_notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['substitution_date', 'original_teacher_id']);
            $table->index(['substitution_date', 'substitute_teacher_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitutions');
        Schema::dropIfExists('room_bookings');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('timetable_slots');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('timetable_templates');
        Schema::dropIfExists('period_definitions');
    }
};
