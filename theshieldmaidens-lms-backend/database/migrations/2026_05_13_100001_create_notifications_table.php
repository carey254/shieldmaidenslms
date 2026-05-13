<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('general');
            $table->string('priority')->default('medium');
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('recipient_type')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('sent');
            $table->timestamps();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
