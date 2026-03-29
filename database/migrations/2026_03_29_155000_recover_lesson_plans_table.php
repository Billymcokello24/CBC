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
        if (!Schema::hasTable('lesson_plans')) {
            Schema::create('lesson_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
                $table->foreignId('class_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
                $table->foreignId('academic_term_id')->constrained()->cascadeOnDelete();
                $table->foreignId('strand_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('sub_strand_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('teaching_plan_id')->nullable();
                $table->string('title');
                $table->string('week_number', 20)->nullable();
                $table->date('lesson_date');
                $table->integer('period_number')->nullable();
                $table->integer('duration_minutes')->default(35);
                $table->text('specific_objectives')->nullable();
                $table->text('learning_outcomes')->nullable();
                $table->text('key_vocabulary')->nullable();
                $table->text('teaching_aids')->nullable();
                $table->text('references')->nullable();
                $table->text('introduction')->nullable();
                $table->text('lesson_development')->nullable();
                $table->text('teacher_activities')->nullable();
                $table->text('learner_activities')->nullable();
                $table->text('conclusion')->nullable();
                $table->text('assessment_methods')->nullable();
                $table->text('reflection')->nullable();
                $table->text('homework')->nullable();
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->text('feedback')->nullable();
                $table->boolean('is_taught')->default(false);
                $table->timestamp('taught_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['teacher_id', 'lesson_date']);
                $table->index(['class_id', 'subject_id', 'lesson_date']);
                
                // Foreign key for teaching_plan_id (will be added if teaching_plans exists)
                $table->foreign('teaching_plan_id')->references('id')->on('teaching_plans')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as this is a recovery migration
    }
};
