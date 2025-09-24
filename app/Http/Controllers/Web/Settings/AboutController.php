<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function index()
    {
        $appInfo = [
            'name' => config('app.name', 'WalletQ'),
            'version' => '1.0.0',
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
        ];

        $systemInfo = [
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
        ];

        return Inertia::render('Settings/About/Index', [
            'appInfo' => $appInfo,
            'systemInfo' => $systemInfo,
        ]);
    }
}