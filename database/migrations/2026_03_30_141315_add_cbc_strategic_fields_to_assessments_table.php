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
        Schema::table('assessments', function (Blueprint $table) {
            $table->json('core_competencies')->nullable()->after('subject_id');
            $table->json('pci')->nullable()->after('core_competencies');
            $table->json('indicators')->nullable()->after('pci');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn(['core_competencies', 'pci', 'indicators']);
        });
    }
};
