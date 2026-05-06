<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->string('type')->default('assignment'); // assignment, quiz, exam, project
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->decimal('passing_score', 8, 2)->default(60.00);
            $table->integer('time_limit_minutes')->nullable(); // For timed assignments
            $table->integer('attempts_allowed')->default(1);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('auto_grade')->default(false);
            $table->text('submission_requirements')->nullable(); // File types, etc.
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'is_published', 'due_date']);
            $table->index(['type', 'available_from', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
