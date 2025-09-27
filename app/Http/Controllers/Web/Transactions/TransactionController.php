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
        $month = $request->get('month', now()->format('Y-m'));

        // Get monthly transactions
        $transactions = $this->transactionService->getMonthlyTransactionsGroupedByDate($user, $month);

        // Get monthly summary
        $summary = $this->transactionService->getMonthlySummary($user, $month);

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
        ];

        return Inertia::render('Transactions/Index', [
            'transactions' => [
                'data' => $transactions,
                'summary' => $summary,
                'master_data' => $masterData,
                'filters' => [
                    'month' => $month,
                ],
            ],
            'pageTitle' => 'Transaksi',
        ]);
    }
}
