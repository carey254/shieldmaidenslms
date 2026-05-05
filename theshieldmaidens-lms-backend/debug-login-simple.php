<?php
// Simple test to debug login
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';

// Get the admin user
$user = \App\Models\User::where('email', 'admin@theshieldmaidens.org')->first();

echo "=== USER DEBUG ===\n";
echo "User found: " . ($user ? 'YES' : 'NO') . "\n";
if ($user) {
    echo "User ID: " . $user->id . "\n";
    echo "User Name: " . $user->name . "\n";
    echo "User Email: " . $user->email . "\n";
    echo "User Role: " . $user->role . "\n";
    echo "User is_admin: " . ($user->is_admin ? 'TRUE' : 'FALSE') . "\n";
    echo "Password Hash: " . substr($user->password, 0, 20) . "...\n";
    echo "Password Check (admin123): " . (\Illuminate\Support\Facades\Hash::check('admin123', $user->password) ? 'MATCHES' : 'DOES NOT MATCH') . "\n";
    
    // Test token creation
    try {
        $token = $user->createToken('auth_token')->plainTextToken;
        echo "Token Creation: SUCCESS\n";
        echo "Token: " . substr($token, 0, 20) . "...\n";
    } catch (Exception $e) {
        echo "Token Creation: FAILED - " . $e->getMessage() . "\n";
    }
} else {
    echo "ERROR: Admin user not found in database!\n";
}

echo "\n=== TESTING MANUAL LOGIN ===\n";

// Test the actual login logic from AuthController
$credentials = [
    'email' => 'admin@theshieldmaidens.org',
    'password' => 'admin123'
];

// Find user by email
$testUser = \App\Models\User::where('email', $credentials['email'])->first();

if (!$testUser || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $testUser->password)) {
    echo "LOGIN RESULT: FAILED - Invalid credentials\n";
} else {
    echo "LOGIN RESULT: SUCCESS\n";
    echo "Token would be created successfully\n";
}
