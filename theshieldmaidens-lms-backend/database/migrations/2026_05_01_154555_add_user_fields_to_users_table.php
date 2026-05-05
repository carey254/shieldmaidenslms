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
            $table->string('name')->nullable()->after('id');
            $table->string('password')->nullable()->after('password_hash');
            $table->boolean('is_admin')->default(false)->after('role');
            $table->string('department')->nullable()->after('status');
            $table->timestamp('last_login_at')->nullable()->after('department');
            $table->timestamp('email_verified_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'password', 'is_admin', 'department', 'last_login_at', 'email_verified_at']);
        });
    }
};
