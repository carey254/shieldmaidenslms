<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating student test user...\n";

$student = new \App\Models\User();
$student->name = 'Test Student';
$student->email = 'student@theshieldmaidens.org';
$student->password = \Illuminate\Support\Facades\Hash::make('student123');
$student->role = 'student';
$student->is_admin = false;
$student->is_instructor = false;
$student->email_verified_at = now();
$student->save();

echo "Student created successfully!\n";
echo "Email: student@theshieldmaidens.org\n";
echo "Password: student123\n";
echo "Password verification: " . (\Illuminate\Support\Facades\Hash::check('student123', $student->password) ? '✓ Correct' : '✗ Incorrect') . "\n";
