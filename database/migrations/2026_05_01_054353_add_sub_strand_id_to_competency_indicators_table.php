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
        Schema::table('competency_indicators', function (Blueprint $table) {
            if (!Schema::hasColumn('competency_indicators', 'sub_strand_id')) {
                $table->foreignId('sub_strand_id')->after('grade_level_id')->nullable()->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('competency_indicators', 'code')) {
                $table->string('code', 50)->after('indicator')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('competency_indicators', function (Blueprint $table) {
            $table->dropForeign(['sub_strand_id']);
            $table->dropColumn(['sub_strand_id', 'code']);
        });
    }
};
