<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::with('category')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $categories = AccountCategory::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return Inertia::render('settings/Accounts', [
            'accounts' => $accounts,
            'categories' => $categories,
        ]);
    }
}