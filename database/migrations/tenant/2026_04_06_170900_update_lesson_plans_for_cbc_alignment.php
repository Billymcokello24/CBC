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
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->integer('number_of_learners')->nullable()->after('lesson_date');
            $table->json('values')->nullable()->after('core_competencies');
            $table->json('life_skills')->nullable()->after('values');
            $table->text('learning_activities')->nullable()->after('lesson_development'); // For Activity 1, 2, 3 structure
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropColumn(['number_of_learners', 'values', 'life_skills', 'learning_activities']);
        });
    }
};
