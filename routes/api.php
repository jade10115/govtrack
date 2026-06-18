<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\RequestTypeController;
use App\Http\Controllers\Api\ClientRequestController;
use App\Http\Controllers\Api\AssignedRequestController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserLevelController;
    Route::get('/cors-test', function () {
    return response()->json([
        'success' => true,
        'message' => 'CORS working'
    ]);
    Route::get('/env-test', function () {
    return response()->json([
        'host' => env('DB_HOST'),
        'database' => env('DB_DATABASE'),
        'port' => env('DB_PORT'),
        'user' => env('DB_USERNAME'),
        'session_driver' => env('SESSION_DRIVER'),
    ]);
});
});
Route::post('/login', [AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class,'profile']);
    Route::post('/logout', [AuthController::class,'logout']);

    Route::apiResource('users', UserController::class);
    Route::apiResource('user-levels', UserLevelController::class);
    Route::apiResource('departments', DepartmentController::class);

    Route::apiResource('request-types', RequestTypeController::class);

    Route::apiResource('requests', ClientRequestController::class);

    Route::get(
        '/assigned-requests',
        [AssignedRequestController::class,'myRequests']
    );

    Route::post(
        '/assigned-requests/{id}/accept',
        [AssignedRequestController::class,'accept']
    );

    Route::post(
        '/assigned-requests/{id}/reject',
        [AssignedRequestController::class,'reject']
    );

    Route::post(
        '/assigned-requests/{id}/complete',
        [AssignedRequestController::class,'complete']
    );

    Route::get(
        '/dashboard',
        [DashboardController::class,'index']
    );


});