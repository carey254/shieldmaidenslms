<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@theshieldmaidens.org',
                'password' => 'admin123',
                'is_admin' => true,
                'role' => 'admin'
            ],
            [
                'name' => 'John Instructor',
                'email' => 'john.instructor@theshieldmaidens.org',
                'password' => 'instructor123',
                'is_admin' => false,
                'role' => 'instructor'
            ],
            [
                'name' => 'Jane Student',
                'email' => 'jane.student@theshieldmaidens.org',
                'password' => 'student123',
                'is_admin' => false,
                'role' => 'student'
            ],
            [
                'name' => 'Mary Parent',
                'email' => 'mary.parent@theshieldmaidens.org',
                'password' => 'parent123',
                'is_admin' => false,
                'role' => 'parent'
            ],
            [
                'name' => 'Test Student',
                'email' => 'test@example.com',
                'password' => 'password',
                'is_admin' => false,
                'role' => 'student'
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                    'is_admin' => $userData['is_admin'] ?? false,
                    'role' => $userData['role'] ?? 'student',
                ]
            );
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Login credentials:');
        foreach ($users as $userData) {
            $this->command->info("Email: {$userData['email']}, Password: {$userData['password']}");
        }
    }
}
