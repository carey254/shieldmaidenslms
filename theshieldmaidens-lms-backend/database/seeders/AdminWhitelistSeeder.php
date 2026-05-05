<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminWhitelistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admin_whitelist')->insert([
            ['email' => 'admin@theshieldmaidens.org'],
            ['email' => 'shi3ldmaidens@gmail.com'],
        ]);
    }
}
