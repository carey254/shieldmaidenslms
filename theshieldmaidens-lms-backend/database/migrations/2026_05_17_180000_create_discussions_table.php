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
        Schema::create('discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('category')->default('general');
            $table->integer('views')->default(0);
            $table->integer('replies_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'course_id']);
            $table->index('category');
            $table->index('created_at');
        });

        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discussion_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->foreignId('parent_id')->nullable()->constrained('discussion_replies')->onDelete('cascade');
            $table->timestamps();

            $table->index(['discussion_id', 'user_id']);
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussions');
    }
};
