<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// The Secret Database Builder Route
Route::get('/setup-database', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return 'SUCCESS: Database tables created perfectly!';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});