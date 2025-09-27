<?php

namespace App\Http\Controllers\Web\Transactions;

use App\Http\Controllers\Controller;
use App\Services\Transaction\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display transaction list page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all filter parameters
        $filters = $this->getFiltersFromRequest($request);

        // Get filtered transactions
        $transactions = $this->transactionService->getFilteredTransactionsGroupedByDate($user, $filters);

        // Get filtered summary
        $summary = $this->transactionService->getFilteredSummary($user, $filters);

        // Get master data for filters
        $masterData = [
            'accounts' => $this->transactionService->getUserAccounts($user),
            'income_categories' => $this->transactionService->getUserTransactionCategories($user)->where('type', 'income')->values()->toArray(),
            'expense_categories' => $this->transactionService->getUserTransactionCategories($user)->where('type', 'expense')->values()->toArray(),
            'tags' => $this->transactionService->getUserTags($user),
            'flag_options' => [
                ['value' => 'normal', 'label' => 'Normal'],
                ['value' => 'transfer_in', 'label' => 'Transfer Masuk'],
                ['value' => 'transfer_out', 'label' => 'Transfer Keluar'],
                ['value' => 'debt_payment', 'label' => 'Pembayaran Hutang'],
                ['value' => 'debt_collect', 'label' => 'Penagihan Piutang'],
                ['value' => 'initial_balance', 'label' => 'Saldo Awal'],
            ],
            'type_options' => [
                ['value' => 'both', 'label' => 'Semua Tipe'],
                ['value' => 'income', 'label' => 'Pemasukan'],
                ['value' => 'expense', 'label' => 'Pengeluaran'],
            ],
            'period_options' => [
                ['value' => 'today', 'label' => 'Hari Ini'],
                ['value' => 'week', 'label' => 'Minggu Ini'],
                ['value' => 'month', 'label' => 'Bulan Ini'],
                ['value' => 'year', 'label' => 'Tahun Ini'],
                ['value' => 'all', 'label' => 'Semua Waktu'],
                ['value' => 'custom', 'label' => 'Pilih Tanggal'],
            ],
        ];

        return Inertia::render('Transactions/Index', [
            'transactions' => [
                'data' => $transactions,
                'summary' => $summary,
                'master_data' => $masterData,
                'filters' => $filters,
            ],
            'pageTitle' => 'Transaksi',
        ]);
    }

    /**
     * Show create transaction form
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $masterData = $this->transactionService->getCreateFormData($user);

        return Inertia::render('Transactions/Create', [
            'masterData' => $masterData,
            'pageTitle' => 'Tambah Transaksi',
        ]);
    }

    /**
     * Store new transaction(s)
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        // Simple validation dengan pesan bahasa Indonesia
        $request->validate([
            'transactions' => 'required|array|min:1',
            'transactions.*.type' => 'required|in:income,expense',
            'transactions.*.account_id' => 'required|exists:accounts,id',
            'transactions.*.transaction_category_id' => 'required|exists:transaction_categories,id',
            'transactions.*.amount' => 'required|numeric|min:0.01',
            'transactions.*.date' => 'required|date',
            'transactions.*.note' => 'nullable|string|max:255',
            'transactions.*.flag' => 'nullable|string',
            'transactions.*.tag_ids' => 'nullable|array',
            'transactions.*.tag_ids.*' => 'exists:tags,id',
        ], [
            'transactions.required' => 'Data transaksi harus diisi.',
            'transactions.*.type.required' => 'Tipe transaksi harus dipilih.',
            'transactions.*.type.in' => 'Tipe transaksi tidak valid.',
            'transactions.*.account_id.required' => 'Akun harus dipilih.',
            'transactions.*.account_id.exists' => 'Akun yang dipilih tidak ditemukan.',
            'transactions.*.transaction_category_id.required' => 'Kategori harus dipilih.',
            'transactions.*.transaction_category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'transactions.*.amount.required' => 'Jumlah harus diisi.',
            'transactions.*.amount.numeric' => 'Jumlah harus berupa angka.',
            'transactions.*.amount.min' => 'Jumlah minimal adalah 0.01.',
            'transactions.*.date.required' => 'Tanggal harus diisi.',
            'transactions.*.date.date' => 'Format tanggal tidak valid.',
            'transactions.*.note.max' => 'Catatan maksimal 255 karakter.',
            'transactions.*.tag_ids.*.exists' => 'Tag yang dipilih tidak ditemukan.',
        ]);

        try {
            $transactionData = $request->input('transactions');
            
            if (count($transactionData) === 1) {
                // Single transaction
                $transaction = $this->transactionService->createTransaction($user, $transactionData[0]);
                return redirect()->route('transactions.index')
                    ->with('success', 'Transaksi berhasil ditambahkan');
            } else {
                // Bulk transactions
                $transactions = $this->transactionService->bulkCreateTransactions($user, $transactionData);
                return redirect()->route('transactions.index')
                    ->with('success', 'Berhasil menambahkan ' . count($transactions) . ' transaksi');
            }
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Get filters from request
     */
    protected function getFiltersFromRequest(Request $request): array
    {
        $filters = [];

        // Date filters
        $filters['period'] = $request->get('period', 'month');
        $filters['date_from'] = $request->get('date_from');
        $filters['date_to'] = $request->get('date_to');
        $filters['month'] = $request->get('month', now()->format('Y-m'));
        $filters['year'] = $request->get('year');

        // Transaction filters
        $filters['type'] = $request->get('type', 'both');
        $filters['search'] = $request->get('search');

        // Array filters - convert comma separated to array
        $filters['account_ids'] = $this->convertToArray($request->get('account_ids'));
        $filters['category_ids'] = $this->convertToArray($request->get('category_ids'));
        $filters['tag_ids'] = $this->convertToArray($request->get('tag_ids'));
        $filters['flags'] = $this->convertToStringArray($request->get('flags'));

        // Amount filters
        $filters['amount_min'] = $request->get('amount_min') ? (float) $request->get('amount_min') : null;
        $filters['amount_max'] = $request->get('amount_max') ? (float) $request->get('amount_max') : null;

        // Don't filter out empty arrays, we need them for UI state
        return array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Convert comma-separated string to array of integers
     */
    protected function convertToArray($value): array
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return array_map('intval', array_filter($value));
        }
        
        $result = array_filter(explode(',', $value));
        return array_map('intval', $result); // Convert to integers for IDs
    }

    /**
     * Convert comma-separated string to array of strings
     */
    protected function convertToStringArray($value): array
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return array_filter($value);
        }
        
        return array_filter(explode(',', $value));
    }

    /**
     * Show edit transaction form
     */
    public function edit(Request $request, $id)
    {
        $user = $request->user();
        
        try {
            $transaction = $this->transactionService->getTransactionById($user, $id);
            $masterData = $this->transactionService->getCreateFormData($user);
            $parseTransaction = $transaction->toArray();
            $parseTransaction['amount'] = (int) $parseTransaction['amount'];

            return Inertia::render('Transactions/Edit', [
                'transaction' => $parseTransaction,
                'masterData' => $masterData,
                'pageTitle' => 'Edit Transaksi',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')
                ->with('error', 'Transaksi tidak ditemukan');
        }
    }

    /**
     * Update transaction
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        
        // Simple validation untuk single transaction
        $request->validate([
            'type' => 'required|in:income,expense',
            'account_id' => 'required|exists:accounts,id',
            'transaction_category_id' => 'required|exists:transaction_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string|max:255',
            'flag' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ], [
            'type.required' => 'Tipe transaksi harus dipilih.',
            'type.in' => 'Tipe transaksi tidak valid.',
            'account_id.required' => 'Akun harus dipilih.',
            'account_id.exists' => 'Akun yang dipilih tidak ditemukan.',
            'transaction_category_id.required' => 'Kategori harus dipilih.',
            'transaction_category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'amount.required' => 'Jumlah harus diisi.',
            'amount.numeric' => 'Jumlah harus berupa angka.',
            'amount.min' => 'Jumlah minimal adalah 0.01.',
            'date.required' => 'Tanggal harus diisi.',
            'date.date' => 'Format tanggal tidak valid.',
            'note.max' => 'Catatan maksimal 255 karakter.',
            'tag_ids.*.exists' => 'Tag yang dipilih tidak ditemukan.',
        ]);

        try {
            $transaction = $this->transactionService->updateTransaction($user, $id, $request->all());
            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal memperbarui transaksi: ' . $e->getMessage()])->withInput();
        }
    }
}
