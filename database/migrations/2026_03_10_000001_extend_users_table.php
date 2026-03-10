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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending')->after('avatar');
            $table->string('locale', 10)->default('en')->after('status');
            $table->string('timezone', 50)->default('Africa/Nairobi')->after('locale');
            $table->timestamp('last_login_at')->nullable()->after('timezone');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->boolean('force_password_change')->default(false)->after('last_login_ip');
            $table->timestamp('password_changed_at')->nullable()->after('force_password_change');
            $table->softDeletes();

            $table->index(['status', 'email_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'avatar',
                'status',
                'locale',
                'timezone',
                'last_login_at',
                'last_login_ip',
                'force_password_change',
                'password_changed_at',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
