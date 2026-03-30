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
        Schema::table('scheme_entries', function (Blueprint $table) {
            $table->integer('duration_minutes')->default(35)->after('lesson_number');
            $table->date('lesson_date')->nullable()->after('duration_minutes');
            $table->text('key_vocabulary')->nullable()->after('topic');
            $table->text('references')->nullable()->after('resources');
            $table->text('introduction')->nullable()->after('learning_activities');
            $table->text('lesson_development')->nullable()->after('introduction');
            $table->text('teacher_activities')->nullable()->after('lesson_development');
            $table->text('conclusion')->nullable()->after('teacher_activities');
            $table->text('reflection')->nullable()->after('remarks');
            $table->text('homework')->nullable()->after('reflection');
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
                'conclusion', 'reflection', 'homework'
            ]);
        });
    }
};
