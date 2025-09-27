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
                $query->where('name', 'like', '%' . $search . '%');
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
            'type' => 'nullable|string|in:cash,bank,e-wallet',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_default'] = false;

        AccountCategory::create($validated);

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Account category created successfully.');
    }

    public function show(AccountCategory $accountCategory)
    {
        if ($accountCategory->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus kategori akun milik orang lain.');
        }

        return Inertia::render('Settings/AccountCategories/Show', [
            'category' => $accountCategory,
        ]);
    }

    public function edit(AccountCategory $accountCategory)
    {
        if ($accountCategory->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat mengubah kategori akun milik orang lain.');
        }

        return Inertia::render('Settings/AccountCategories/Edit', [
            'category' => $accountCategory,
        ]);
    }

    public function update(Request $request, AccountCategory $accountCategory)
    {
        if ($accountCategory->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat mengubah kategori akun milik orang lain.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:cash,bank,e-wallet',
        ]);

        $accountCategory->update($validated);

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Account category updated successfully.');
    }

    public function destroy(AccountCategory $accountCategory)
    {
        if ($accountCategory->user_id !== Auth::id()) {
            return back()->with('error', 'Tidak dapat menghapus kategori akun milik orang lain.');
        }

        $accountCategory->delete();

        return redirect()
            ->route('settings.account-categories.index')
            ->with('success', 'Kategori akun berhasil dihapus.');
    }
}
