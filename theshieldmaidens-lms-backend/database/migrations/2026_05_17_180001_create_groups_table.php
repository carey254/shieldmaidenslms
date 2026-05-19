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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facilitator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->integer('max_members')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['facilitator_id', 'course_id']);
            $table->index('code');
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // member, leader
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
            $table->index(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};
