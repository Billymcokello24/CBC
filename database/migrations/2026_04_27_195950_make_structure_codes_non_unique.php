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
        Schema::table('grade_levels', function (Blueprint $table) {
            // Add non-unique index first to satisfy FK requirements
            $table->index(['school_id', 'code'], 'grade_levels_school_id_code_idx');
            
            $indices = DB::select("SHOW INDEXES FROM grade_levels WHERE Key_name = 'grade_levels_school_id_code_unique'");
            if (count($indices) > 0) {
                $table->dropUnique('grade_levels_school_id_code_unique');
            }
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->index(['school_id', 'code'], 'streams_school_id_code_idx');
            
            $indices = DB::select("SHOW INDEXES FROM streams WHERE Key_name = 'streams_school_id_code_unique'");
            if (count($indices) > 0) {
                $table->dropUnique('streams_school_id_code_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'code']);
            $table->unique(['school_id', 'code']);
        });

        Schema::table('streams', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'code']);
            $table->unique(['school_id', 'code']);
        });
    }
};
