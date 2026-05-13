<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('opportunities')) {
            return;
        }

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('training');
            $table->string('organization');
            $table->string('location')->nullable();
            $table->boolean('remote_option')->default(false);
            $table->timestamp('deadline');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('application_link')->nullable();
            $table->string('contact_email');
            $table->string('visibility')->default('all');
            $table->string('status')->default('active');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['visibility', 'status', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
