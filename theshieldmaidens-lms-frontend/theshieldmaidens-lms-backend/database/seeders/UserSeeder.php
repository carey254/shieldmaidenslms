<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@shieldmaidens.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Instructor User
        $instructor = User::create([
            'name' => 'Faith Jeptoo',
            'email' => 'faith@shieldmaidens.com',
            'password' => Hash::make('instructor123'),
            'role' => 'instructor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create Student User
        $student = User::create([
            'name' => 'John Student',
            'email' => 'student@shieldmaidens.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create additional test users
        $users = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@shieldmaidens.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael@shieldmaidens.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily@shieldmaidens.com',
                'password' => Hash::make('password123'),
                'role' => 'instructor',
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        $this->command->info('✅ Default users created successfully!');
        $this->command->info('📧 Admin Login: admin@shieldmaidens.com / admin123');
        $this->command->info('👨‍🏫 Instructor Login: faith@shieldmaidens.com / instructor123');
        $this->command->info('👨‍🎓 Student Login: student@shieldmaidens.com / student123');
    }
}
