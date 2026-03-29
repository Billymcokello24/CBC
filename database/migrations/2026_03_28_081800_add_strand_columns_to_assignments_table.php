<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'strand_id')) {
                $table->unsignedBigInteger('strand_id')->nullable()->after('subject_id');
                $table->foreign('strand_id')->references('id')->on('strands')->nullOnDelete();
            }
            if (!Schema::hasColumn('assignments', 'sub_strand_id')) {
                $table->unsignedBigInteger('sub_strand_id')->nullable()->after('strand_id');
                $table->foreign('sub_strand_id')->references('id')->on('sub_strands')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'sub_strand_id')) {
                $table->dropForeign(['sub_strand_id']);
                $table->dropColumn('sub_strand_id');
            }
            if (Schema::hasColumn('assignments', 'strand_id')) {
                $table->dropForeign(['strand_id']);
                $table->dropColumn('strand_id');
            }
        });
    }
};
