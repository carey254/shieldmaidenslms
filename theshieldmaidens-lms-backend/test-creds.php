<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing admin credentials...\n";

$admin = \App\Models\User::where('email', 'admin@theshieldmaidens.org')->first();
if($admin) {
    echo "Email: admin@theshieldmaidens.org\n";
    echo "Password: admin123\n";
    echo "Password check: " . (\Illuminate\Support\Facades\Hash::check('admin123', $admin->password) ? '✓ Correct' : '✗ Incorrect') . "\n";
} else {
    echo "Admin not found\n";
}

echo "\nTesting instructor credentials...\n";

$instructor = \App\Models\User::where('email', 'john.instructor@theshieldmaidens.org')->first();
if($instructor) {
    echo "Email: john.instructor@theshieldmaidens.org\n";
    echo "Password: instructor123\n";
    echo "Password check: " . (\Illuminate\Support\Facades\Hash::check('instructor123', $instructor->password) ? '✓ Correct' : '✗ Incorrect') . "\n";
} else {
    echo "Instructor not found\n";
}
