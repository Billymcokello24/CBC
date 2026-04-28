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
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code', 100)->change();
        });

        Schema::table('staff_categories', function (Blueprint $table) {
            $table->string('code', 100)->change();
        });

        Schema::table('staff_designations', function (Blueprint $table) {
            $table->string('code', 100)->change();
        });

        if (Schema::hasTable('grade_levels')) {
            Schema::table('grade_levels', function (Blueprint $table) {
                $table->string('code', 100)->change();
            });
        }

        if (Schema::hasTable('streams')) {
            Schema::table('streams', function (Blueprint $table) {
                $table->string('code', 100)->change();
            });
        }

        if (Schema::hasTable('classes')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->string('code', 100)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code', 20)->change();
        });

        Schema::table('staff_categories', function (Blueprint $table) {
            $table->string('code', 20)->change();
        });

        Schema::table('staff_designations', function (Blueprint $table) {
            $table->string('code', 20)->change();
        });
    }
};
