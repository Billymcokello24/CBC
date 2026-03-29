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
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->index(); // Tenant isolation
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_strand_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_indicator_id')->nullable()->constrained('learning_indicators')->nullOnDelete();
            $table->foreignId('competency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('max_score', 8, 2)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['assessment_id', 'sub_strand_id']);
            $table->index(['competency_id', 'sub_strand_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_items');
    }
};
