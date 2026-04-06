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
            if (!Schema::hasColumn('lesson_plans', 'number_of_learners')) {
                $table->integer('number_of_learners')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'values')) {
                $table->json('values')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'life_skills')) {
                $table->json('life_skills')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'learning_activities')) {
                $table->json('learning_activities')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'core_competencies')) {
                $table->json('core_competencies')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'pci')) {
                $table->json('pci')->nullable();
            }
            if (!Schema::hasColumn('lesson_plans', 'inquiry_questions')) {
                $table->text('inquiry_questions')->nullable();
            }
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
