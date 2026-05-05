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
            $table->integer('login_attempts')->default(0);
            $table->timestamp('last_login_attempt_at')->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->integer('password_reset_attempts')->default(0);
            $table->timestamp('last_password_reset_attempt_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'login_attempts',
                'last_login_attempt_at',
                'locked_until',
                'password_reset_attempts',
                'last_password_reset_attempt_at'
            ]);
        });
    }
};
