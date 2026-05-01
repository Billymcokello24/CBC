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
        Schema::table('assessment_items', function (Blueprint $table) {
            $table->foreignId('competency_indicator_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_items', function (Blueprint $table) {
            $table->foreignId('competency_indicator_id')->nullable(false)->change();
        });
    }
};
