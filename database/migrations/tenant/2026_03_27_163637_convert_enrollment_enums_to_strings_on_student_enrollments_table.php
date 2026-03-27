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
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->string('enrollment_type')->change();
            $table->string('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->enum('enrollment_type', ['new', 'promoted', 'repeated', 'transferred_in', 'rejoined'])->change();
            $table->enum('status', ['active', 'completed', 'transferred', 'withdrawn', 'promoted', 'repeated'])->change();
        });
    }
};
