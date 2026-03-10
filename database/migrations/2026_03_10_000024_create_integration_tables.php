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
        // API Keys
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->string('secret_hash');
            $table->json('permissions')->nullable();
            $table->json('ip_whitelist')->nullable();
            $table->integer('rate_limit')->default(60);
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Integration Configurations
        Schema::create('integration_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('integration_name'); // mpesa, sms_gateway, email_provider, etc.
            $table->string('provider', 100);
            $table->json('credentials')->nullable(); // encrypted
            $table->json('settings')->nullable();
            $table->string('environment', 20)->default('sandbox'); // sandbox, production
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('last_test_success')->nullable();
            $table->text('last_test_message')->nullable();
            $table->foreignId('configured_by')->constrained('users');
            $table->timestamps();

            $table->unique(['school_id', 'integration_name']);
        });

        // Webhook Endpoints
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->string('secret_hash')->nullable();
            $table->json('events'); // events to subscribe to
            $table->json('headers')->nullable();
            $table->integer('timeout_seconds')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Webhook Events
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('event_key')->unique();
            $table->text('description')->nullable();
            $table->string('category', 100);
            $table->json('payload_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Webhook Logs
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->string('event_key');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['pending', 'success', 'failed', 'retrying'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'status']);
            $table->index(['event_key', 'created_at']);
        });

        // External System Mappings
        Schema::create('external_system_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('external_system', 100); // nemis, knec, bank_system, etc.
            $table->string('local_entity_type', 100); // student, teacher, payment
            $table->unsignedBigInteger('local_entity_id');
            $table->string('external_entity_id');
            $table->json('external_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->enum('sync_status', ['synced', 'pending', 'error'])->default('pending');
            $table->text('sync_error')->nullable();
            $table->timestamps();

            $table->unique(['external_system', 'local_entity_type', 'local_entity_id'], 'external_mapping_unique');
            $table->index(['external_system', 'external_entity_id']);
        });

        // Integration Logs
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('integration_name');
            $table->string('action', 100);
            $table->string('direction', 20); // inbound, outbound
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->integer('response_code')->nullable();
            $table->enum('status', ['success', 'failed', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();

            $table->index(['integration_name', 'created_at']);
            $table->index(['school_id', 'status']);
        });

        // Scheduled Jobs
        Schema::create('scheduled_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('job_class');
            $table->string('schedule'); // cron expression
            $table->json('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->enum('last_status', ['success', 'failed', 'running'])->nullable();
            $table->text('last_error')->nullable();
            $table->integer('run_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Job History
        Schema::create('job_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_job_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_class');
            $table->json('parameters')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->enum('status', ['running', 'completed', 'failed']);
            $table->text('output')->nullable();
            $table->text('error')->nullable();
            $table->integer('memory_usage_mb')->nullable();
            $table->timestamps();

            $table->index(['job_class', 'started_at']);
        });

        // SMS Messages Log
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_phone', 20);
            $table->nullableMorphs('recipient'); // student, guardian, teacher
            $table->string('message_type', 100); // fee_reminder, attendance, announcement, etc.
            $table->text('message');
            $table->string('provider', 50)->nullable();
            $table->string('provider_message_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->decimal('cost', 8, 4)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status', 'created_at']);
        });

        // Email Messages Log
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_email');
            $table->nullableMorphs('recipient');
            $table->string('message_type', 100);
            $table->string('subject');
            $table->longText('body');
            $table->json('attachments')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('provider_message_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'bounced', 'failed'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status', 'created_at']);
        });

        // System Settings (global)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string');
            $table->string('group', 100)->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('job_history');
        Schema::dropIfExists('scheduled_jobs');
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('external_system_mappings');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('integration_configs');
        Schema::dropIfExists('api_keys');
    }
};
