<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/debug-login', function() {
    $user = User::where('email', 'admin@theshieldmaidens.org')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    
    $passwordCheck = Hash::check('admin123', $user->password);
    
    return response()->json([
        'user_found' => $user ? true : false,
        'user_id' => $user->id ?? null,
        'user_email' => $user->email ?? null,
        'user_role' => $user->role ?? null,
        'user_is_admin' => $user->is_admin ?? false,
        'password_matches' => $passwordCheck,
        'password_hash' => substr($user->password, 0, 20) . '...'
    ]);
});
