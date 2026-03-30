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
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->foreignId('learning_outcome_id')->nullable()->after('academic_year_id')->constrained()->nullOnDelete();
            $table->string('achievement_level', 20)->nullable()->after('learning_outcome_id'); // EE, ME, AE, BE
            $table->foreignId('assessed_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_achievements', function (Blueprint $table) {
            $table->dropForeign(['learning_outcome_id']);
            $table->dropForeign(['assessed_by']);
            $table->dropColumn(['learning_outcome_id', 'achievement_level', 'assessed_by']);
        });
    }
};
