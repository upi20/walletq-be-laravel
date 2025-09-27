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
            'income_categories' => $this->transactionService->getUserTransactionCategories($user)->where('type', 'income'),
            'expense_categories' => $this->transactionService->getUserTransactionCategories($user)->where('type', 'expense'),
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
        $filters['flags'] = $this->convertToArray($request->get('flags'));

        // Amount filters
        $filters['amount_min'] = $request->get('amount_min') ? (float) $request->get('amount_min') : null;
        $filters['amount_max'] = $request->get('amount_max') ? (float) $request->get('amount_max') : null;

        return array_filter($filters, function($value) {
            return $value !== null && $value !== '' && $value !== [];
        });
    }

    /**
     * Convert comma-separated string to array
     */
    protected function convertToArray($value): ?array
    {
        if (empty($value)) {
            return null;
        }
        
        if (is_array($value)) {
            return array_filter($value);
        }
        
        return array_filter(explode(',', $value));
    }
}
