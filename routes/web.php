<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return view('welcome');
});

// The Secret Database Builder Route
Route::get('/setup-database', function () {
    try {
        // We removed the seeder so it doesn't throw a Duplicate Entry error!
        // We are ONLY forcing the database to accept your exact login credentials:
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123')
            ]
        );

        return 'SUCCESS: Admin account (admin@admin.com / password123) is locked and loaded!';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});