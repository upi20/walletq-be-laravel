<?php

namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use App\Services\Transaction\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Report Controller
 * 
 * NEW CONTROLLER - Created for Reports feature
 * Does NOT modify any existing controllers
 */
class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private TransactionService $transactionService
    ) {}

    /**
     * Display reports index page
     * 
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $this->getFiltersFromRequest($request);

        // Use existing TransactionService - NO MODIFICATIONS
        $summary = $this->transactionService->getFilteredSummary($user, $filters);

        // Use NEW ReportService
        $incomeByCategory = $this->reportService->getIncomeByCategory($user, $filters);
        $expenseByCategory = $this->reportService->getExpenseByCategory($user, $filters);

        // Get master data using existing service - NO MODIFICATIONS
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

        return Inertia::render('Reports/Index', [
            'summary' => $summary,
            'incomeByCategory' => $incomeByCategory,
            'expenseByCategory' => $expenseByCategory,
            'masterData' => $masterData,
            'filters' => $filters,
        ]);
    }

    /**
     * Get filters from request
     * COPIED from TransactionController - not modifying original
     * 
     * @param Request $request
     * @return array
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

        // Array filters
        $filters['account_ids'] = $this->convertToArray($request->get('account_ids'));
        $filters['category_ids'] = $this->convertToArray($request->get('category_ids'));
        $filters['tag_ids'] = $this->convertToArray($request->get('tag_ids'));
        $filters['flags'] = $this->convertToStringArray($request->get('flags'));

        // Amount filters
        $filters['amount_min'] = $request->get('amount_min') ? (float) $request->get('amount_min') : null;
        $filters['amount_max'] = $request->get('amount_max') ? (float) $request->get('amount_max') : null;

        return array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Convert to array
     * COPIED from TransactionController - not modifying original
     * 
     * @param mixed $value
     * @return array
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
        return array_map('intval', $result);
    }

    /**
     * Convert to string array
     * COPIED from TransactionController - not modifying original
     * 
     * @param mixed $value
     * @return array
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
}
