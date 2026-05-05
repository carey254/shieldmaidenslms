<?php
// Test API directly
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Test login endpoint
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_POST = json_encode([
    'email' => 'admin@theshieldmaidens.org',
    'password' => 'admin123'
]);

// Create request
$request = \Illuminate\Http\Request::capture();

// Test AuthController
$controller = new \App\Http\Controllers\Api\AuthController();

try {
    echo "Testing login endpoint...\n";
    
    // Create login request
    $loginRequest = new \App\Http\Requests\Api\LoginRequest();
    $loginRequest->merge([
        'email' => 'admin@theshieldmaidens.org',
        'password' => 'admin123'
    ]);
    
    // Validate
    if ($loginRequest->fails()) {
        echo "Validation failed: " . $loginRequest->errors()->first() . "\n";
        exit;
    }
    
    echo "Validation passed\n";
    echo "Credentials: " . json_encode($loginRequest->validated()) . "\n";
    
    // Test login
    $response = $controller->login($loginRequest);
    
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
