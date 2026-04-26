<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration consolidates missing columns for scheme_entries that were 
     * previously only defined in tenant migrations but are needed in the main database.
     */
    public function up(): void
    {
        Schema::table('scheme_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('scheme_entries', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(35)->after('lesson_number');
            }
            if (!Schema::hasColumn('scheme_entries', 'lesson_date')) {
                $table->date('lesson_date')->nullable()->after('duration_minutes');
            }
            if (!Schema::hasColumn('scheme_entries', 'key_vocabulary')) {
                $table->text('key_vocabulary')->nullable()->after('topic');
            }
            if (!Schema::hasColumn('scheme_entries', 'references')) {
                $table->text('references')->nullable()->after('resources');
            }
            if (!Schema::hasColumn('scheme_entries', 'introduction')) {
                $table->text('introduction')->nullable()->after('learning_activities');
            }
            if (!Schema::hasColumn('scheme_entries', 'lesson_development')) {
                $table->text('lesson_development')->nullable()->after('introduction');
            }
            if (!Schema::hasColumn('scheme_entries', 'teacher_activities')) {
                $table->text('teacher_activities')->nullable()->after('lesson_development');
            }
            if (!Schema::hasColumn('scheme_entries', 'conclusion')) {
                $table->text('conclusion')->nullable()->after('teacher_activities');
            }
            if (!Schema::hasColumn('scheme_entries', 'reflection')) {
                $table->text('reflection')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('scheme_entries', 'homework')) {
                $table->text('homework')->nullable()->after('reflection');
            }
            if (!Schema::hasColumn('scheme_entries', 'core_competencies')) {
                $table->json('core_competencies')->nullable()->after('learning_activities');
            }
            if (!Schema::hasColumn('scheme_entries', 'pci')) {
                $table->json('pci')->nullable()->after('core_competencies');
            }
            if (!Schema::hasColumn('scheme_entries', 'inquiry_questions')) {
                $table->text('inquiry_questions')->nullable()->after('pci');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheme_entries', function (Blueprint $table) {
            $table->dropColumn([
                'duration_minutes', 'lesson_date', 'key_vocabulary', 'references',
                'introduction', 'lesson_development', 'teacher_activities',
                'conclusion', 'reflection', 'homework', 'core_competencies', 'pci', 'inquiry_questions'
            ]);
        });
    }
};
