<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (portal: /admin/login)
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@theshieldmaidens.org',
            'password' => 'Admin123',
            'is_admin' => true,
            'role' => 'admin',
            'status' => 'active',
            'department' => 'IT',
        ]);

        // Create instructor user (portal: /instructor/login)
        User::create([
            'name' => 'Portal Instructor',
            'email' => 'instructor@theshieldmaidens.org',
            'password' => 'Instructor123',
            'is_admin' => false,
            'role' => 'instructor',
            'status' => 'active',
            'department' => 'Computer Science',
        ]);

        // Additional instructor (optional demo account)
        User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@theshieldmaidens.org',
            'password' => 'password',
            'is_admin' => false,
            'role' => 'instructor',
            'status' => 'active',
            'department' => 'Computer Science',
        ]);

        // Create student users
        User::create([
            'name' => 'Caren',
            'email' => 'caren@theshieldmaidens.org',
            'password' => 'password',
            'is_admin' => false,
            'role' => 'student',
            'status' => 'active',
            'department' => 'Software Development',
        ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john@theshieldmaidens.org',
            'password' => 'password',
            'is_admin' => false,
            'role' => 'student',
            'status' => 'active',
            'department' => 'Data Science',
        ]);
    }
}
