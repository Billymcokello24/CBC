<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_resources', function (Blueprint $blueprint) {
            $blueprint->foreignId('folder_id')->nullable()->after('grade_level_id')->constrained('resource_folders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_resources', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['folder_id']);
            $blueprint->dropColumn('folder_id');
        });
    }
};
