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
            // Add stream_id if not already present
            if (!Schema::hasColumn('student_enrollments', 'stream_id')) {
                $table->foreignId('stream_id')->nullable()->after('class_id')->constrained('streams')->nullOnDelete();
            }

            // Admission details
            if (!Schema::hasColumn('student_enrollments', 'previous_school')) {
                $table->string('previous_school')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('student_enrollments', 'entry_type')) {
                $table->string('entry_type')->nullable()->after('previous_school');
            }
            if (!Schema::hasColumn('student_enrollments', 'sponsor_type')) {
                $table->string('sponsor_type')->nullable()->after('entry_type');
            }
            if (!Schema::hasColumn('student_enrollments', 'boarding_status')) {
                $table->string('boarding_status')->nullable()->after('sponsor_type');
            }
            if (!Schema::hasColumn('student_enrollments', 'admission_number')) {
                $table->string('admission_number')->nullable()->after('student_id');
            }
        });

        // Handle unique constraint update defensively
        Schema::table('student_enrollments', function (Blueprint $table) {
            $oldIndex = 'student_enrollments_student_id_class_id_academic_year_id_unique';
            $indexes = DB::select("SHOW INDEX FROM student_enrollments WHERE Key_name = ?", [$oldIndex]);
            
            if (count($indexes) > 0) {
                 // We must drop foreign keys that might be using this index
                try { $table->dropForeign(['student_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['class_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['academic_year_id']); } catch (\Exception $e) {}

                $table->dropUnique(['student_id', 'class_id', 'academic_year_id']);
                
                // Re-add foreign keys
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
                $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
                $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            }

            // Check if new index exists
            $newIndexes = DB::select("SHOW INDEX FROM student_enrollments WHERE Key_name = 'student_academic_year_unique'");
            if (count($newIndexes) === 0) {
                $table->unique(['student_id', 'academic_year_id'], 'student_academic_year_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropUnique('student_academic_year_unique');
            
            $table->dropForeign(['student_id']);
            $table->dropForeign(['class_id']);
            $table->dropForeign(['academic_year_id']);
            
            $table->unique(['student_id', 'class_id', 'academic_year_id']);

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->onDelete('cascade');
            
            $table->dropColumn([
                'stream_id',
                'admission_number',
                'previous_school',
                'entry_type',
                'sponsor_type',
                'boarding_status'
            ]);
        });
    }
};
