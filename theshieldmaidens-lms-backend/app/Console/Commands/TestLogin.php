<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestLogin extends Command
{
    protected $signature = 'test:login';
    protected $description = 'Test login credentials';

    public function handle()
    {
        $this->info('Testing login credentials...');
        
        // Check existing users
        $users = User::all();
        $this->info("\nExisting users:");
        foreach ($users as $user) {
            $this->line("ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}");
        }
        
        // Test student login
        $studentEmail = 'jane.student@theshieldmaidens.org';
        $studentPassword = 'student123';
        
        $student = User::where('email', $studentEmail)->first();
        if ($student) {
            $this->info("\nTesting student login:");
            $this->line("Email: {$studentEmail}");
            $this->line("Password: {$studentPassword}");
            $this->line("Password check: " . (Hash::check($studentPassword, $student->password) ? '✓ Correct' : '✗ Incorrect'));
        } else {
            $this->info("\nStudent user not found. Creating...");
            $student = User::create([
                'name' => 'Jane Student',
                'email' => $studentEmail,
                'password' => Hash::make($studentPassword),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
            $this->info("Student user created successfully!");
        }
        
        // Test alternative student login
        $altEmail = 'test@example.com';
        $altPassword = 'password';
        
        $altStudent = User::where('email', $altEmail)->first();
        if ($altStudent) {
            $this->info("\nTesting alternative student login:");
            $this->line("Email: {$altEmail}");
            $this->line("Password: {$altPassword}");
            $this->line("Password check: " . (Hash::check($altPassword, $altStudent->password) ? '✓ Correct' : '✗ Incorrect'));
        } else {
            $this->info("\nAlternative student user not found. Creating...");
            $altStudent = User::create([
                'name' => 'Test Student',
                'email' => $altEmail,
                'password' => Hash::make($altPassword),
                'role' => 'student',
                'email_verified_at' => now()
            ]);
            $this->info("Alternative student user created successfully!");
        }
        
        $this->info("\n=== FINAL CREDENTIALS ===");
        $this->info("Student 1 - Email: {$studentEmail}, Password: {$studentPassword}");
        $this->info("Student 2 - Email: {$altEmail}, Password: {$altPassword}");
        
        return Command::SUCCESS;
    }
}
