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
        Schema::table('student_assessment_ratings', function (Blueprint $table) {
            $table->decimal('marks', 8, 2)->nullable()->after('score');
            $table->integer('out_of')->default(100)->after('marks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_assessment_ratings', function (Blueprint $table) {
            $table->dropColumn(['marks', 'out_of']);
        });
    }
};
