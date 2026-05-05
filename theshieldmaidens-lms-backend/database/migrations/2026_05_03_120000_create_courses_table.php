<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('difficulty_level')->default('beginner'); // beginner|intermediate|advanced
            $table->string('duration')->nullable(); // human label e.g. "4 weeks"
            $table->unsignedInteger('duration_hours')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('max_students')->default(9999);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('visibility')->default('public'); // public|private|scholarship
            $table->string('status')->default('draft'); // draft|review|published|archived
            $table->unsignedSmallInteger('modules_count')->default(0);
            $table->string('thumbnail')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
