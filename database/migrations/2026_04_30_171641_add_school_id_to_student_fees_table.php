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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->foreignId('school_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Populate school_id for existing records from their associated student
        if (Schema::hasTable('student_fees') && Schema::hasTable('students')) {
            \Illuminate\Support\Facades\DB::table('student_fees')
                ->join('students', 'student_fees.student_id', '=', 'students.id')
                ->update(['student_fees.school_id' => \Illuminate\Support\Facades\DB::raw('students.school_id')]);
        }

        Schema::table('student_fees', function (Blueprint $table) {
            // If you want to make it non-nullable after populating
            // $table->foreignId('school_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
