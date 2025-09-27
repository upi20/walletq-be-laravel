<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Get filtered transactions
     */
    public function getFilteredTransactions(User $user, array $filters = [])
    {
        $query = Transaction::with(['account.category', 'category', 'tags'])
            ->where('user_id', $user->id);

        // Apply date filters
        $this->applyDateFilters($query, $filters);

        // Apply other filters
        $this->applyTypeFilter($query, $filters);
        $this->applyAccountFilter($query, $filters);
        $this->applyCategoryFilter($query, $filters);
        $this->applyTagFilter($query, $filters);
        $this->applyFlagFilter($query, $filters);
        $this->applyAmountFilter($query, $filters);
        $this->applySearchFilter($query, $filters);

        return $query->orderBy('date', 'desc')
            // ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Apply date filters
     */
    protected function applyDateFilters($query, array $filters)
    {
        $period = $filters['period'] ?? 'month';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        if ($period === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        } elseif ($period === 'today') {
            $query->whereDate('date', Carbon::today());
        } elseif ($period === 'week') {
            $query->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $targetMonth = $month ?: Carbon::now()->format('Y-m');
            $startDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
        } elseif ($period === 'year') {
            $targetYear = $year ?: Carbon::now()->year;
            $query->whereYear('date', $targetYear);
        }
        // 'all' period = no date filter
    }

    /**
     * Apply type filter
     */
    protected function applyTypeFilter($query, array $filters)
    {
        $type = $filters['type'] ?? null;
        if ($type && $type !== 'both') {
            $query->where('type', $type);
        }
    }

    /**
     * Apply account filter
     */
    protected function applyAccountFilter($query, array $filters)
    {
        $accountIds = $filters['account_ids'] ?? null;
        if ($accountIds && is_array($accountIds) && !empty($accountIds)) {
            $query->whereIn('account_id', $accountIds);
        }
    }

    /**
     * Apply category filter
     */
    protected function applyCategoryFilter($query, array $filters)
    {
        $categoryIds = $filters['category_ids'] ?? null;
        if ($categoryIds && is_array($categoryIds) && !empty($categoryIds)) {
            $query->whereIn('transaction_category_id', $categoryIds);
        }
    }

    /**
     * Apply tag filter
     */
    protected function applyTagFilter($query, array $filters)
    {
        $tagIds = $filters['tag_ids'] ?? null;
        if ($tagIds && is_array($tagIds) && !empty($tagIds)) {
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }
    }

    /**
     * Apply flag filter
     */
    protected function applyFlagFilter($query, array $filters)
    {
        $flags = $filters['flags'] ?? null;
        if ($flags && is_array($flags) && !empty($flags)) {
            $query->whereIn('flag', $flags);
        }
    }

    /**
     * Apply amount filter
     */
    protected function applyAmountFilter($query, array $filters)
    {
        $amountMin = $filters['amount_min'] ?? null;
        $amountMax = $filters['amount_max'] ?? null;

        if ($amountMin !== null) {
            $query->where('amount', '>=', $amountMin);
        }
        if ($amountMax !== null) {
            $query->where('amount', '<=', $amountMax);
        }
    }

    /**
     * Apply search filter
     */
    protected function applySearchFilter($query, array $filters)
    {
        $search = $filters['search'] ?? null;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                    ->orWhereHas('account', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Get filtered transactions grouped by date
     */
    public function getFilteredTransactionsGroupedByDate(User $user, array $filters = [])
    {
        $transactions = $this->getFilteredTransactions($user, $filters);
        $transactionGroupedByDate = $transactions->groupBy(function ($transaction) {
            return $transaction->date->format('Y-m-d');
        });

        // set carbon to indonesia
        Carbon::setLocale('id');
        $result = [];
        foreach ($transactionGroupedByDate as $date => $transactions) {
            $now = Carbon::parse($date);
            $data = (object)[];
            $data->label = $now->format('d');
            $data->day = $now->format('l');
            $data->month = $now->format('F Y');
            $data->date = Carbon::parse($date);

            // amount
            $data->amount = 0;
            foreach ($transactions as $transaction) {
                $transaction->date = Carbon::parse($transaction->date);
                $data->amount += ($transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
            }
            // order transaction by date desc
            $transactions = collect($transactions)->sortByDesc('date');

            $data->transactions = $transactions;
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Get filtered summary
     */
    public function getFilteredSummary(User $user, array $filters = []): array
    {
        $transactions = $this->getFilteredTransactions($user, $filters);

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $transactionCount = $transactions->count();

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $totalIncome - $totalExpense,
            'transaction_count' => $transactionCount,
        ];
    }

    /**
     * Get all accounts for user (for filter options)
     */
    public function getUserAccounts(User $user)
    {
        return $user->accounts()->orderBy('name')->with('category')->get();
    }

    /**
     * Get all transaction categories for user (for filter options)
     */
    public function getUserTransactionCategories(User $user)
    {
        return $user->transactionCategories()->orderBy('name')->get();
    }

    /**
     * Get all tags for user (for filter options)
     */
    public function getUserTags(User $user)
    {
        return $user->tags()->get();
    }

    /**
     * Create a single transaction
     */
    public function createTransaction(User $user, array $data): Transaction
    {
        // remove all characer except number
        $data['amount'] = preg_replace('/[^0-9]/', '', $data['amount']);
        // Validate and prepare data
        $transactionData = $this->prepareTransactionData($user, $data);

        // Create transaction
        $transaction = Transaction::create($transactionData);

        // Attach tags if provided
        if (!empty($data['tag_ids'])) {
            $transaction->tags()->attach($data['tag_ids']);
        }

        // Update account balance
        if ($transaction->account) {
            $transaction->account->updateBalance($transaction->amount, $transaction->type);
        }

        return $transaction->load(['account', 'category', 'tags']);
    }

    /**
     * Bulk create transactions
     */
    public function bulkCreateTransactions(User $user, array $transactions): array
    {
        $createdTransactions = [];

        foreach ($transactions as $transactionData) {
            try {
                $createdTransactions[] = $this->createTransaction($user, $transactionData);
            } catch (\Exception $e) {
                // Log error but continue with other transactions
                Log::error('Failed to create transaction: ' . $e->getMessage(), $transactionData);
            }
        }

        return $createdTransactions;
    }

    /**
     * Prepare transaction data for creation
     */
    protected function prepareTransactionData(User $user, array $data): array
    {
        return [
            'user_id' => $user->id,
            'account_id' => $data['account_id'],
            'transaction_category_id' => $data['transaction_category_id'],
            'type' => $data['type'],
            'amount' => (float) $data['amount'],
            'date' => $data['date'] ?? now(),
            'note' => $data['note'] ?? null,
            'flag' => $data['flag'] ?? Transaction::FLAG_NORMAL,
        ];
    }

    /**
     * Get master data for create form
     */
    public function getCreateFormData(User $user): array
    {
        return [
            'accounts' => $this->getUserAccounts($user),
            'income_categories' => $this->getUserTransactionCategories($user)->where('type', 'income')->values()->toArray(),
            'expense_categories' => $this->getUserTransactionCategories($user)->where('type', 'expense')->values()->toArray(),
            'tags' => $this->getUserTags($user),
            'flag_options' => [
                ['value' => Transaction::FLAG_NORMAL, 'label' => 'Normal'],
                ['value' => Transaction::FLAG_TRANSFER_IN, 'label' => 'Transfer Masuk'],
                ['value' => Transaction::FLAG_TRANSFER_OUT, 'label' => 'Transfer Keluar'],
                ['value' => Transaction::FLAG_DEBT_PAYMENT, 'label' => 'Pembayaran Hutang'],
                ['value' => Transaction::FLAG_DEBT_COLLECT, 'label' => 'Penagihan Piutang'],
                ['value' => Transaction::FLAG_INITIAL_BALANCE, 'label' => 'Saldo Awal'],
            ],
        ];
    }

    /**
     * Get transaction by ID for specific user
     */
    public function getTransactionById(User $user, int $id): Transaction
    {
        return Transaction::with(['account.category', 'category', 'tags'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
    }

    /**
     * Update transaction
     */
    public function updateTransaction(User $user, int $id, array $data): Transaction
    {
        $hasUpdateAmount = false;
        $transaction = $this->getTransactionById($user, $id);
        // if amount or type is updated, recalculate account balance
        if ($transaction->amount != $data['amount']) {
            $hasUpdateAmount = true;
        }

        // if type is updated, recalculate account balance
        if ($transaction->type != $data['type']) {
            $hasUpdateAmount = true;
        }

        DB::beginTransaction();

        // update transaction
        $transaction->update($data);

        // recalculate account balance
        if ($hasUpdateAmount) {
            $transaction->account->recalculateBalance();
        }

        DB::commit();
        return $transaction->load(['account', 'category', 'tags']);
    }
}
