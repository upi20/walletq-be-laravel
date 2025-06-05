<?php

use Illuminate\Support\Facades\Route;

// Serve the frontend static file
Route::get('/{any?}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
