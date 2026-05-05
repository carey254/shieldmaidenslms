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
        Schema::create('facilitator_invites', function (Blueprint $table) {
            $table->id();
            $table->string('email')->notNull();
            $table->text('token')->unique()->notNull();
            $table->timestamp('expires_at')->notNull();
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending')->notNull();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilitator_invites');
    }
};
