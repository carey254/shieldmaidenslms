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
            // Drop old columns
            $table->dropColumn(['name', 'password', 'email_verified_at', 'remember_token']);
            
            // Add new columns
            $table->text('password_hash')->nullable()->after('email');
            $table->enum('role', ['student', 'facilitator', 'admin'])->notNull()->after('password_hash');
            $table->enum('status', ['active', 'pending_setup', 'disabled'])->default('active')->notNull()->after('role');
            $table->enum('auth_provider', ['local', 'google'])->default('local')->notNull()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['password_hash', 'role', 'status', 'auth_provider']);
            $table->string('name')->after('id');
            $table->string('password')->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->rememberToken();
        });
    }
};
