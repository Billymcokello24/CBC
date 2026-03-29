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
        $tables = [
            'learning_areas',
            'subjects',
            'strands',
            'sub_strands',
            'competencies',
            'competency_indicators',
            'learning_outcomes',
            'learning_indicators',
            'curriculum_resources',
            'scheme_entries',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'school_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('school_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'learning_areas',
            'subjects',
            'strands',
            'sub_strands',
            'competencies',
            'competency_indicators',
            'learning_outcomes',
            'learning_indicators',
            'curriculum_resources',
            'scheme_entries',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'school_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['school_id']);
                    $table->dropColumn('school_id');
                });
            }
        }
    }
};
