<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Test or create admin user
$admin = App\Models\User::where('email', 'admin@theshieldmaidens.org')->first();

if ($admin) {
    echo "Admin found: " . $admin->name . " (" . $admin->email . ")\n";
    
    // Test current password
    if (Hash::check('admin123', $admin->password)) {
        echo "Current password: admin123 ✓\n";
    } else {
        // Set password to admin123
        $admin->password = Hash::make('admin123');
        $admin->save();
        echo "Password set to: admin123\n";
    }
} else {
    echo "Admin not found, creating...\n";
    $admin = App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@theshieldmaidens.org',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'is_admin' => true,
        'email_verified_at' => now()
    ]);
    echo "Admin created with password: admin123\n";
}

echo "\n=== ADMIN CREDENTIALS ===\n";
echo "Email: admin@theshieldmaidens.org\n";
echo "Password: admin123\n";
