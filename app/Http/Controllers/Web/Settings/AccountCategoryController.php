<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\AccountCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountCategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $categories = AccountCategory::where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Settings/AccountCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/AccountCategories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        AccountCategory::create($validated);

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Account category created successfully.');
    }

    public function show(AccountCategory $accountCategory)
    {
        $this->authorize('view', $accountCategory);

        return Inertia::render('Settings/AccountCategories/Show', [
            'category' => $accountCategory,
        ]);
    }

    public function edit(AccountCategory $accountCategory)
    {
        $this->authorize('update', $accountCategory);

        return Inertia::render('Settings/AccountCategories/Edit', [
            'category' => $accountCategory,
        ]);
    }

    public function update(Request $request, AccountCategory $accountCategory)
    {
        $this->authorize('update', $accountCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? $accountCategory->is_active;

        $accountCategory->update($validated);

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Account category updated successfully.');
    }

    public function destroy(AccountCategory $accountCategory)
    {
        $this->authorize('delete', $accountCategory);

        // Check if category has accounts
        if ($accountCategory->accounts()->exists()) {
            return back()->with('error', 'Cannot delete category that has accounts.');
        }

        $accountCategory->delete();

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Account category deleted successfully.');
    }
}