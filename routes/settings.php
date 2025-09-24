<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\Web\Settings\AboutController;
use App\Http\Controllers\Web\Settings\AccountCategoryController;
use App\Http\Controllers\Web\Settings\AccountController;
use App\Http\Controllers\Web\Settings\TagController;
use App\Http\Controllers\Web\Settings\TransactionCategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::get('settings', function () {
        return Inertia::render('Settings/Index');
    })->name('settings.index');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    // Master Data Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        // Accounts
        Route::resource('accounts', AccountController::class);
        
        // Account Categories
        Route::resource('account-categories', AccountCategoryController::class);
        
        // Transaction Categories
        Route::resource('transaction-categories', TransactionCategoryController::class);
        
        // Tags
        Route::resource('tags', TagController::class);
        
        // About
        Route::get('about', [AboutController::class, 'index'])->name('about');
    });
});
