<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $users = \App\Models\User::all(['id', 'name', 'email', 'role', 'password']);
    echo "=== USERS IN DATABASE ===\n";
    foreach ($users as $user) {
        echo "ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
        echo "Password Hash: " . substr($user->password, 0, 20) . "...\n";
        echo "---\n";
    }
    
    echo "\n=== TESTING PASSWORD VERIFICATION ===\n";
    $admin = \App\Models\User::where('email', 'admin@theshieldmaidens.org')->first();
    if ($admin) {
        echo "Admin user found\n";
        echo "Testing password 'admin123': " . (Illuminate\Support\Facades\Hash::check('admin123', $admin->password) ? 'MATCHES' : 'DOES NOT MATCH') . "\n";
    } else {
        echo "Admin user NOT found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
