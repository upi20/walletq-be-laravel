<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $accounts = Account::with('category')
            ->where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->get();

        $categories = AccountCategory::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Settings/Accounts/Index', [
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        $categories = AccountCategory::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Settings/Accounts/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_category_id' => 'required|exists:account_categories,id',
            'initial_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['initial_balance'] = $validated['initial_balance'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Account::create($validated);

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function show(Account $account)
    {
        $this->authorize('view', $account);

        $account->load('category');

        return Inertia::render('Settings/Accounts/Show', [
            'account' => $account,
        ]);
    }

    public function edit(Account $account)
    {
        $this->authorize('update', $account);

        $categories = AccountCategory::where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        return Inertia::render('Settings/Accounts/Edit', [
            'account' => $account,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Account $account)
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'account_category_id' => 'required|exists:account_categories,id',
            'initial_balance' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $validated['initial_balance'] = $validated['initial_balance'] ?? $account->initial_balance;
        $validated['is_active'] = $validated['is_active'] ?? $account->is_active;

        $account->update($validated);

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        $this->authorize('delete', $account);

        // Check if account has transactions
        if ($account->transactions()->exists() || $account->transfersFrom()->exists() || $account->transfersTo()->exists()) {
            return back()->with('error', 'Cannot delete account that has transactions.');
        }

        $account->delete();

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}