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
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competency_indicator_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->decimal('weight', 5, 2)->default(1);
            $table->decimal('max_score', 8, 2)->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['assessment_id', 'competency_indicator_id'], 'assess_item_indicator_unique');
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
