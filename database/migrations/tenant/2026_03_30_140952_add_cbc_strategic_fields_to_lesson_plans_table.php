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
            $table->json('core_competencies')->nullable()->after('learning_outcomes');
            $table->json('pci')->nullable()->after('core_competencies');
            $table->text('inquiry_questions')->nullable()->after('pci');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_plans', function (Blueprint $table) {
            $table->dropColumn(['core_competencies', 'pci', 'inquiry_questions']);
        });
    }
};
