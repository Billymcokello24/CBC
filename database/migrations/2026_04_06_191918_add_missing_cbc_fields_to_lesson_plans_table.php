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
            if (!Schema::hasColumn('lesson_plans', 'learning_activities')) {
                $table->json('learning_activities')->nullable()->after('introduction');
            }
            if (!Schema::hasColumn('lesson_plans', 'number_of_learners')) {
                $table->integer('number_of_learners')->nullable()->after('lesson_date');
            }
            if (!Schema::hasColumn('lesson_plans', 'core_competencies')) {
                $table->json('core_competencies')->nullable()->after('learning_outcomes');
            }
            if (!Schema::hasColumn('lesson_plans', 'values')) {
                $table->json('values')->nullable()->after('core_competencies');
            }
            if (!Schema::hasColumn('lesson_plans', 'life_skills')) {
                $table->json('life_skills')->nullable()->after('values');
            }
            if (!Schema::hasColumn('lesson_plans', 'pci')) {
                $table->json('pci')->nullable()->after('assessment_methods');
            }
            if (!Schema::hasColumn('lesson_plans', 'inquiry_questions')) {
                $table->text('inquiry_questions')->nullable()->after('pci');
            }
            if (!Schema::hasColumn('lesson_plans', 'scheme_entry_id')) {
                $table->foreignId('scheme_entry_id')->nullable()->after('teaching_plan_id')->constrained('scheme_entries')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropForeign(['scheme_entry_id']);
            $table->dropColumn([
                'number_of_learners',
                'core_competencies',
                'values',
                'life_skills',
                'pci',
                'inquiry_questions',
                'scheme_entry_id'
            ]);
        });
    }
};
