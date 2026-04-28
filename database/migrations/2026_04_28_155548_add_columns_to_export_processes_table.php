<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('export_processes', function (Blueprint $table) {
            if (!Schema::hasColumn('export_processes', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('export_processes', 'school_id')) {
                $table->foreignId('school_id')->after('user_id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('export_processes', 'type')) {
                $table->string('type')->after('school_id');
            }
            if (!Schema::hasColumn('export_processes', 'status')) {
                $table->string('status')->default('pending')->after('type');
            }
            if (!Schema::hasColumn('export_processes', 'file_path')) {
                $table->string('file_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('export_processes', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_path');
            }
            if (!Schema::hasColumn('export_processes', 'error_message')) {
                $table->text('error_message')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('export_processes', 'filters')) {
                $table->json('filters')->nullable()->after('error_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('export_processes', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'school_id', 'type', 'status', 'file_path', 'file_name', 'error_message', 'filters']);
        });
    }
};
