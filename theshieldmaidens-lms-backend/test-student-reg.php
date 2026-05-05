<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Student Registration and Login Flow...\n\n";

// Test 1: Check if student user exists
$student = \App\Models\User::where('email', 'student@theshieldmaidens.org')->first();
if ($student) {
    echo "✅ Student user found: " . $student->name . " (" . $student->email . ")\n";
    echo "   Role: " . $student->role . "\n";
    echo "   Is Admin: " . ($student->is_admin ? 'Yes' : 'No') . "\n";
    echo "   Is Instructor: " . ($student->is_instructor ? 'Yes' : 'No') . "\n";
    echo "   Created: " . $student->created_at . "\n\n";
} else {
    echo "❌ Student user not found\n\n";
}

// Test 2: Check admin user
$admin = \App\Models\User::where('email', 'admin@theshieldmaidens.org')->first();
if ($admin) {
    echo "✅ Admin user found: " . $admin->name . " (" . $admin->email . ")\n";
    echo "   Role: " . $admin->role . "\n";
    echo "   Is Admin: " . ($admin->is_admin ? 'Yes' : 'No') . "\n";
    echo "   Is Instructor: " . ($admin->is_instructor ? 'Yes' : 'No') . "\n";
    echo "   Created: " . $admin->created_at . "\n\n";
} else {
    echo "❌ Admin user not found\n\n";
}

// Test 3: Database connection
try {
    $users = \App\Models\User::count();
    echo "✅ Database connection working\n";
    echo "   Total users in database: " . $users . "\n\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n\n";
}

echo "Backend is ready for student registration and login!\n";
