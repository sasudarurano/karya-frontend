<?php

use Illuminate\Support\Facades\Route;

// Quick debug route untuk mendapatkan token
// Hanya untuk development/debugging
// HAPUS di production!

Route::get('/debug/token', function() {
    if (!config('app.debug')) {
        abort(404);
    }
    
    return response()->json([
        'token' => session('api_token'),
        'user' => session('user'),
        'has_token' => !empty(session('api_token')),
        'token_length' => session('api_token') ? strlen(session('api_token')) : 0,
        'user_role' => session('user.role'),
        'instructions' => [
            'copy_token' => session('api_token'),
            'test_command' => '.\\test-admin-endpoint.ps1 ' . session('api_token')
        ]
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('web');
