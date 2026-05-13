<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (!Schema::hasColumn('announcements', 'category')) {
                $table->string('category')->default('general');
            }
            if (!Schema::hasColumn('announcements', 'priority')) {
                $table->string('priority')->default('medium');
            }
            if (!Schema::hasColumn('announcements', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (!Schema::hasColumn('announcements', 'application_link')) {
                $table->text('application_link')->nullable();
            }
            if (!Schema::hasColumn('announcements', 'show_on_home')) {
                $table->boolean('show_on_home')->default(true);
            }
            if (!Schema::hasColumn('announcements', 'show_in_portals')) {
                $table->boolean('show_in_portals')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            foreach (['category', 'priority', 'is_featured', 'application_link', 'show_on_home', 'show_in_portals'] as $col) {
                if (Schema::hasColumn('announcements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
