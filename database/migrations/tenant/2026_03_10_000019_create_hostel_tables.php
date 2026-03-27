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
        // Hostels
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('gender', ['male', 'female', 'mixed']);
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('warden_name')->nullable();
            $table->string('warden_phone')->nullable();
            $table->integer('total_capacity')->default(0);
            $table->integer('current_occupancy')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
        });

        // Hostel Blocks
        Schema::create('hostel_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->integer('floors')->default(1);
            $table->integer('total_rooms')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hostel_id', 'code']);
        });

        // Room Types
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->integer('capacity');
            $table->text('description')->nullable();
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->text('amenities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Hostel Rooms
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_block_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->string('room_number', 20);
            $table->integer('floor')->default(1);
            $table->integer('capacity');
            $table->integer('current_occupancy')->default(0);
            $table->text('amenities')->nullable();
            $table->enum('status', ['available', 'full', 'maintenance', 'closed'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['hostel_block_id', 'room_number']);
        });

        // Hostel Beds
        Schema::create('hostel_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->string('bed_number', 20);
            $table->enum('bed_type', ['single', 'bunk_upper', 'bunk_lower'])->default('single');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->timestamps();

            $table->unique(['room_id', 'bed_number']);
        });

        // Hostel Allocations
        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained('hostel_beds')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('allocation_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'ended', 'transferred', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('allocated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
            $table->index(['room_id', 'status']);
        });

        // Hostel Fees
        Schema::create('hostel_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['room_type_id', 'academic_year_id', 'academic_term_id'], 'hostel_fee_room_year_term_unique');
        });

        // Room Inspections
        Schema::create('room_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->time('inspection_time');
            $table->string('inspector_name');
            $table->enum('cleanliness', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->enum('maintenance', ['excellent', 'good', 'fair', 'poor'])->nullable();
            $table->text('issues_found')->nullable();
            $table->text('actions_required')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->constrained('users');
            $table->timestamps();
        });

        // Hostel Visitors
        Schema::create('hostel_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_name');
            $table->string('relationship');
            $table->string('id_number', 50)->nullable();
            $table->string('phone')->nullable();
            $table->date('visit_date');
            $table->time('check_in_time');
            $table->time('check_out_time')->nullable();
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'visit_date']);
        });

        // Hostel Exeat (leave passes)
        Schema::create('hostel_exeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hostel_allocation_id')->constrained()->cascadeOnDelete();
            $table->string('exeat_type', 100); // weekend, emergency, medical, holiday
            $table->date('departure_date');
            $table->time('departure_time')->nullable();
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->time('actual_return_time')->nullable();
            $table->string('destination');
            $table->string('guardian_contact');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'departed', 'returned', 'overdue'])->default('pending');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_exeats');
        Schema::dropIfExists('hostel_visitors');
        Schema::dropIfExists('room_inspections');
        Schema::dropIfExists('hostel_fees');
        Schema::dropIfExists('hostel_allocations');
        Schema::dropIfExists('hostel_beds');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('hostel_blocks');
        Schema::dropIfExists('hostels');
    }
};
