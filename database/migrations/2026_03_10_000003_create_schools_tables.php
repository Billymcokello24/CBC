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
        // School Types (e.g., Primary, Secondary, Mixed)
        Schema::create('school_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // School Levels (e.g., National, County, Sub-County, Private)
        Schema::create('school_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Main Schools Table
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique(); // KNEC or Ministry code
            $table->string('registration_number')->unique()->nullable();
            $table->foreignId('school_type_id')->constrained('school_types');
            $table->foreignId('school_level_id')->constrained('school_levels');
            $table->string('logo')->nullable();
            $table->string('motto')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('sub_county', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->date('established_date')->nullable();
            $table->enum('gender_type', ['mixed', 'boys', 'girls'])->default('mixed');
            $table->enum('boarding_type', ['day', 'boarding', 'day_and_boarding'])->default('day');
            $table->integer('student_capacity')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending_approval'])->default('pending_approval');
            $table->string('timezone', 50)->default('Africa/Nairobi');
            $table->string('currency', 10)->default('KES');
            $table->string('locale', 10)->default('en');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['county', 'sub_county']);
            $table->index('status');
        });

        // School Branches for schools with multiple campuses
        Schema::create('school_branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('county', 100)->nullable();
            $table->string('sub_county', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_main_branch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // School Settings
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string'); // string, boolean, integer, json, array
            $table->string('group', 100)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'key']);
            $table->index(['school_id', 'group']);
        });

        // School Documents (certificates, licenses, etc.)
        Schema::create('school_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type', 100); // license, certificate, registration, etc.
            $table->string('file_path');
            $table->string('file_type', 50)->nullable();
            $table->integer('file_size')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'document_type']);
        });

        // School Contacts (key personnel)
        Schema::create('school_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('contact_type', 100); // principal, deputy, chairperson, secretary
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'contact_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_contacts');
        Schema::dropIfExists('school_documents');
        Schema::dropIfExists('school_settings');
        Schema::dropIfExists('school_branches');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('school_levels');
        Schema::dropIfExists('school_types');
    }
};
