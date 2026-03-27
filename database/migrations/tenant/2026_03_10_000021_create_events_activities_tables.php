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
        // Event Types
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('icon')->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Events
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('venue')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('organizer')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->integer('max_participants')->nullable();
            $table->integer('current_participants')->default(0);
            $table->decimal('registration_fee', 10, 2)->nullable();
            $table->date('registration_deadline')->nullable();
            $table->boolean('requires_registration')->default(false);
            $table->boolean('is_public')->default(true);
            $table->json('target_audience')->nullable(); // students, parents, teachers, all
            $table->json('target_grades')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('attachments')->nullable();
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'start_date', 'status']);
        });

        // Event Venues
        Schema::create('event_venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->integer('capacity')->nullable();
            $table->text('facilities')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('is_internal')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Event Registrations
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->morphs('registrant'); // student, teacher, guardian, or user
            $table->timestamp('registered_at');
            $table->enum('status', ['registered', 'confirmed', 'attended', 'cancelled', 'no_show'])->default('registered');
            $table->boolean('payment_required')->default(false);
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'registrant_type', 'registrant_id'], 'event_reg_unique');
        });

        // Event Resources
        Schema::create('event_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('resource_type', 100); // equipment, material, personnel
            $table->string('name');
            $table->integer('quantity')->default(1);
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('status', ['requested', 'approved', 'allocated', 'returned'])->default('requested');
            $table->timestamps();
        });

        // Clubs
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable(); // academic, sports, arts, service, etc.
            $table->string('logo')->nullable();
            $table->string('motto')->nullable();
            $table->foreignId('patron_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('assistant_patron_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('meeting_day')->nullable();
            $table->time('meeting_time')->nullable();
            $table->string('meeting_venue')->nullable();
            $table->decimal('membership_fee', 10, 2)->nullable();
            $table->integer('max_members')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
        });

        // Club Memberships
        Schema::create('club_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('role', 100)->nullable(); // member, secretary, treasurer, chairperson
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'left'])->default('pending');
            $table->boolean('fee_paid')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['club_id', 'student_id', 'academic_year_id'], 'club_stud_year_unique');
        });

        // Club Activities
        Schema::create('club_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('activity_type', 100); // meeting, competition, trip, workshop
            $table->date('activity_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->integer('attendees_count')->nullable();
            $table->text('outcomes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        // Extracurricular Activities
        Schema::create('extracurricular_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->string('category', 100); // sports, music, drama, debate, etc.
            $table->string('sub_category', 100)->nullable();
            $table->foreignId('coach_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('practice_schedule')->nullable();
            $table->string('venue')->nullable();
            $table->boolean('is_competitive')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Student Activity Participation
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained('extracurricular_activities')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('role', 100)->nullable(); // participant, captain, leader
            $table->string('position', 100)->nullable();
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'left'])->default('active');
            $table->text('achievements')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'activity_id', 'academic_year_id'], 'stud_act_year_unique');
        });

        // Sports/Competition Results
        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained('extracurricular_activities')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('competition_name');
            $table->string('competition_level', 50); // school, zonal, county, regional, national, international
            $table->date('competition_date');
            $table->string('venue')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('award')->nullable();
            $table->text('participants')->nullable();
            $table->text('description')->nullable();
            $table->string('certificate_file')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_results');
        Schema::dropIfExists('student_activities');
        Schema::dropIfExists('extracurricular_activities');
        Schema::dropIfExists('club_activities');
        Schema::dropIfExists('club_memberships');
        Schema::dropIfExists('clubs');
        Schema::dropIfExists('event_resources');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_venues');
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_types');
    }
};
