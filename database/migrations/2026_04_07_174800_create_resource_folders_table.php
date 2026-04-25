<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_folders', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->foreignId('school_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $blueprint->foreignId('grade_level_id')->nullable()->constrained('grade_levels')->nullOnDelete();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_folders');
    }
};
