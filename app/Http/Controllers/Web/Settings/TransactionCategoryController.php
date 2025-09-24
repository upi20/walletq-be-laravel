<?php

namespace App\Http\Controllers\Web\Settings;

use App\Http\Controllers\Controller;
use App\Models\TransactionCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionCategoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $categories = TransactionCategory::where('user_id', Auth::id())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Settings/TransactionCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only('search', 'type'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Settings/TransactionCategories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        TransactionCategory::create($validated);

        return redirect()
            ->route('settings.transaction-categories.index')
            ->with('success', 'Transaction category created successfully.');
    }

    public function show(TransactionCategory $transactionCategory)
    {
        $this->authorize('view', $transactionCategory);

        return Inertia::render('Settings/TransactionCategories/Show', [
            'category' => $transactionCategory,
        ]);
    }

    public function edit(TransactionCategory $transactionCategory)
    {
        $this->authorize('update', $transactionCategory);

        return Inertia::render('Settings/TransactionCategories/Edit', [
            'category' => $transactionCategory,
        ]);
    }

    public function update(Request $request, TransactionCategory $transactionCategory)
    {
        $this->authorize('update', $transactionCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? $transactionCategory->is_active;

        $transactionCategory->update($validated);

        return redirect()
            ->route('settings.transaction-categories.index')
            ->with('success', 'Transaction category updated successfully.');
    }

    public function destroy(TransactionCategory $transactionCategory)
    {
        $this->authorize('delete', $transactionCategory);

        // Check if category has transactions
        if ($transactionCategory->transactions()->exists()) {
            return back()->with('error', 'Cannot delete category that has transactions.');
        }

        $transactionCategory->delete();

        return redirect()
            ->route('settings.transaction-categories.index')
            ->with('success', 'Transaction category deleted successfully.');
    }
}