<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('class_sessions')) {
            return;
        }

        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('meeting_link')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('recording_url')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'starts_at']);
            $table->index(['instructor_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
