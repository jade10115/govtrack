<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// The Secret Database Builder Route
Route::get('/setup-database', function () {
    try {
        // We changed this to db:seed to create your admin account!
        Artisan::call('db:seed', ['--force' => true]);
        return 'SUCCESS: Database seeded perfectly! Admin account created.';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});