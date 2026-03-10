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
        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('registration_number')->unique();
            $table->string('vehicle_type', 100); // bus, van, minibus
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->integer('capacity');
            $table->string('fuel_type', 50)->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->date('inspection_expiry')->nullable();
            $table->string('insurance_document')->nullable();
            $table->string('gps_tracker_id', 100)->nullable();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Drivers
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('id_number', 50)->nullable();
            $table->string('license_number');
            $table->string('license_class', 20)->nullable();
            $table->date('license_expiry');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('photo')->nullable();
            $table->date('date_hired')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Vehicle-Driver Assignments
        Schema::create('vehicle_driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->date('assigned_from');
            $table->date('assigned_to')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Transport Routes
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->text('description')->nullable();
            $table->time('morning_departure_time')->nullable();
            $table->time('afternoon_departure_time')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->decimal('fee_amount', 10, 2)->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // Route Stops
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('stop_order');
            $table->time('morning_pickup_time')->nullable();
            $table->time('afternoon_dropoff_time')->nullable();
            $table->text('landmark')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['route_id', 'stop_order']);
        });

        // Student Transport Assignments
        Schema::create('student_transport', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->foreignId('pickup_stop_id')->nullable()->constrained('route_stops')->nullOnDelete();
            $table->foreignId('dropoff_stop_id')->nullable()->constrained('route_stops')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('transport_type', ['both', 'morning_only', 'afternoon_only'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });

        // Trip Logs
        Schema::create('trip_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->constrained('transport_routes')->cascadeOnDelete();
            $table->date('trip_date');
            $table->enum('trip_type', ['morning', 'afternoon', 'special']);
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->decimal('start_odometer', 10, 2)->nullable();
            $table->decimal('end_odometer', 10, 2)->nullable();
            $table->integer('students_count')->default(0);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('incidents')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'trip_date']);
        });

        // Vehicle Maintenance
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type', 100); // service, repair, inspection
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('vendor')->nullable();
            $table->decimal('odometer_reading', 10, 2)->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->string('invoice_file')->nullable();
            $table->foreignId('reported_by')->constrained('users');
            $table->timestamps();
        });

        // Fuel Logs
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->date('fuel_date');
            $table->decimal('liters', 8, 2);
            $table->decimal('cost_per_liter', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->decimal('odometer_reading', 10, 2)->nullable();
            $table->string('fuel_station')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_file')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
        Schema::dropIfExists('vehicle_maintenance');
        Schema::dropIfExists('trip_logs');
        Schema::dropIfExists('student_transport');
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('vehicle_driver_assignments');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};
