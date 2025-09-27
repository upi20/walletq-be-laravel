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
                $query->where('name', 'like', '%' . $search . '%');
            });
		if($request->has('sortField') && $request->has('sortOrder')) {
			// validation sortField:current_balance, sortOrder:asc|desc
			$allowedSortFields = ['name', 'current_balance', 'created_at'];
			$allowedSortOrders = ['asc', 'desc'];
			if(!in_array($request->sortField, $allowedSortFields) || !in_array($request->sortOrder, $allowedSortOrders)) {
				$accounts->orderBy('name', 'asc');
			}else{
				$accounts->orderBy($request->sortField, $request->sortOrder);
			}
		} else {
			$accounts->orderBy('name', 'asc');
		}
		
		$accounts = $accounts->get();

        $categories = AccountCategory::where('user_id', Auth::id())->get();

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
        ], [
            'name.required' => 'Nama rekening wajib diisi.',
            'name.string' => 'Nama rekening harus berupa teks.',
            'name.max' => 'Nama rekening maksimal 255 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'account_category_id.required' => 'Kategori rekening wajib dipilih.',
            'account_category_id.exists' => 'Kategori rekening tidak valid.',
            'initial_balance.numeric' => 'Saldo awal harus berupa angka.',
            'is_active.boolean' => 'Status aktif harus berupa true atau false.',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['initial_balance'] = $validated['initial_balance'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        Account::create($validated);

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', 'Rekening berhasil ditambahkan.');
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
        if($account->user_id !== Auth::id()) {
            return redirect()
                ->route('settings.accounts.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengedit rekening ini.');
        }

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
        if($account->user_id !== Auth::id()) {
            return redirect()
                ->route('settings.accounts.index')
                ->with('error', 'Anda tidak memiliki izin untuk mengupdate rekening ini.');
        }

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
        $user = Auth::id();
        if($account->user_id !== $user) {
            return redirect()
                ->route('settings.accounts.index')
                ->with('error', 'Anda tidak memiliki izin untuk menghapus rekening ini.');
        }
        $account->delete();
        return redirect()
            ->route('settings.accounts.index')
            ->with('success', 'Rekening berhasil dihapus.');
    }
}