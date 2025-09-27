<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Transactions\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');


Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Transaction routes
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('index');
        Route::get('/data', [TransactionController::class, 'getData'])->name('data');
        Route::get('/export', [TransactionController::class, 'export'])->name('export');
        Route::get('/stats', [TransactionController::class, 'getStats'])->name('stats');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
