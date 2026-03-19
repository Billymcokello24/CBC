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
        Schema::table('rubric_levels', function (Blueprint $table) {
            $table->string('grade_code', 10)->nullable()->after('level_name');
            $table->decimal('min_score', 8, 2)->nullable()->after('points');
            $table->decimal('max_score', 8, 2)->nullable()->after('min_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubric_levels', function (Blueprint $table) {
            $table->dropColumn(['grade_code', 'min_score', 'max_score']);
        });
    }
};
