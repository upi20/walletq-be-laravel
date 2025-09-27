<?php

namespace App\Http\Controllers\Web\Transactions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\FilterTransactionRequest;
use App\Http\Resources\Transaction\TransactionListResource;
use App\Services\Transaction\TransactionService;
use App\Models\Account;
use App\Models\TransactionCategory;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display transaction list page
     */
    public function index(FilterTransactionRequest $request): Response
    {
        $user = $request->user();
        $filters = $request->getValidatedFilters();
        
        // Get filtered transactions
        $transactions = $this->transactionService->getFilteredTransactions($user, $filters);
        
        // Get summary data
        $summary = $this->transactionService->getTransactionSummary($user, $filters);
        
        // Get quick stats
        $quickStats = $this->transactionService->getQuickStats($user);
        
        // Get master data for filters
        $masterData = $this->getMasterDataForFilters($user);
        
        // Prepare resource
        $transactionResource = new TransactionListResource($transactions);
        $transactionResource->withSummary($summary)
                          ->withQuickStats($quickStats)
                          ->withFilters($filters)
                          ->withMasterData($masterData);

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactionResource,
            'pageTitle' => 'Transaksi',
        ]);
    }

    /**
     * Get transactions data via AJAX for dynamic loading
     */
    public function getData(FilterTransactionRequest $request)
    {
        $user = $request->user();
        $filters = $request->getValidatedFilters();
        
        // Get filtered transactions
        $transactions = $this->transactionService->getFilteredTransactions($user, $filters);
        
        // Get summary data
        $summary = $this->transactionService->getTransactionSummary($user, $filters);
        
        // Get quick stats
        $quickStats = $this->transactionService->getQuickStats($user);
        
        // Prepare resource
        $transactionResource = new TransactionListResource($transactions);
        $transactionResource->withSummary($summary)
                          ->withQuickStats($quickStats)
                          ->withFilters($filters);

        return $transactionResource;
    }

    /**
     * Get master data for filters
     */
    private function getMasterDataForFilters($user): array
    {
        return [
            'accounts' => Account::where('user_id', $user->id)
                ->with('category:id,name,type')
                ->select('id', 'name', 'account_category_id', 'current_balance')
                ->orderBy('name')
                ->get()
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'name' => $account->name,
                        'current_balance' => (float) $account->current_balance,
                        'formatted_balance' => 'Rp ' . number_format($account->current_balance, 0, ',', '.'),
                        'category' => $account->category ? [
                            'id' => $account->category->id,
                            'name' => $account->category->name,
                            'type' => $account->category->type,
                        ] : null,
                    ];
                }),

            'income_categories' => TransactionCategory::where('user_id', $user->id)
                ->where('type', 'income')
                ->where('is_hide', false)
                ->select('id', 'name', 'type')
                ->orderBy('name')
                ->get(),

            'expense_categories' => TransactionCategory::where('user_id', $user->id)
                ->where('type', 'expense')
                ->where('is_hide', false)
                ->select('id', 'name', 'type')
                ->orderBy('name')
                ->get(),

            'tags' => Tag::where('user_id', $user->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),

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

            'sort_options' => [
                ['value' => 'date', 'label' => 'Tanggal'],
                ['value' => 'amount', 'label' => 'Jumlah'],
                ['value' => 'account', 'label' => 'Akun'],
                ['value' => 'category', 'label' => 'Kategori'],
            ],

            'per_page_options' => [
                ['value' => 10, 'label' => '10 per halaman'],
                ['value' => 25, 'label' => '25 per halaman'],
                ['value' => 50, 'label' => '50 per halaman'],
                ['value' => 100, 'label' => '100 per halaman'],
            ],
        ];
    }

    /**
     * Export transactions
     */
    public function export(FilterTransactionRequest $request)
    {
        // TODO: Implement export functionality
        // This could export to CSV, Excel, PDF, etc.
        return response()->json(['message' => 'Export feature coming soon']);
    }

    /**
     * Get transaction statistics
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        
        // Get quick stats
        $quickStats = $this->transactionService->getQuickStats($user);
        
        return response()->json([
            'quick_stats' => $quickStats,
        ]);
    }
}
