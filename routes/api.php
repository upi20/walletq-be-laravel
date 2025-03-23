<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Admin\AccountCategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('jwt')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });
    });
    
    // Admin
    Route::prefix('admin')->group(function () {
        Route::prefix('master-data')->group(function () {
            Route::get('/account-categories', [AccountCategoryController::class, 'index']);  // List all categories
            Route::post('/account-categories', [AccountCategoryController::class, 'store']);  // Create new category
            Route::get('/account-categories/{id}', [AccountCategoryController::class, 'show']);  // Show specific category
            Route::put('/account-categories/{id}', [AccountCategoryController::class, 'update']);  // Update category
            Route::delete('/account-categories/{id}', [AccountCategoryController::class, 'destroy']);  // Delete category
        });
    });

    Route::prefix('user')->group(function () {

    });
});
