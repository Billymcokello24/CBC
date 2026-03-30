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
            $table->foreignId('lesson_plan_id')->nullable()->constrained()->onDelete('set null')->after('teacher_id');
            $table->foreignId('sub_strand_id')->nullable()->constrained()->onDelete('set null')->after('subject_id');
        });
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['lesson_plan_id']);
            $table->dropForeign(['sub_strand_id']);
            $table->dropColumn(['lesson_plan_id', 'sub_strand_id']);
        });
    }
};
