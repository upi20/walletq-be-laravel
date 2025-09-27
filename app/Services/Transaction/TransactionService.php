<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class TransactionService
{
    /**
     * Get filtered transactions with pagination
     */
    public function getFilteredTransactions(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Transaction::with([
            'account:id,name,account_category_id',
            'account.category:id,name,type',
            'category:id,name,type',
            'tags:id,name',
            'source'
        ])->where('user_id', $user->id);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $this->applySorting($query, $filters);

        // Paginate
        $perPage = $filters['per_page'] ?? 25;
        return $query->paginate($perPage);
    }

    /**
     * Get transaction summary for given filters
     */
    public function getTransactionSummary(User $user, array $filters = []): array
    {
        $query = Transaction::where('user_id', $user->id);
        $this->applyFilters($query, $filters);

        $summary = $query->selectRaw('
            SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense,
            COUNT(*) as transaction_count
        ')->first();

        $totalIncome = (float) $summary->total_income;
        $totalExpense = (float) $summary->total_expense;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $totalIncome - $totalExpense,
            'transaction_count' => $summary->transaction_count,
            'period_label' => $this->getPeriodLabel($filters),
        ];
    }

    /**
     * Get quick stats (today, this week, this month)
     */
    public function getQuickStats(User $user): array
    {
        $now = Carbon::now();
        
        return [
            'today' => $this->getStatsForPeriod($user, $now->copy()->startOfDay(), $now->copy()->endOfDay()),
            'this_week' => $this->getStatsForPeriod($user, $now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'this_month' => $this->getStatsForPeriod($user, $now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        // Date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date', '<=', $filters['date_to']);
        }

        // Date preset filters
        if (!empty($filters['date_preset']) && $filters['date_preset'] !== 'custom') {
            $this->applyDatePreset($query, $filters['date_preset']);
        }

        // Account filters
        if (!empty($filters['account_ids']) && is_array($filters['account_ids'])) {
            $query->whereIn('account_id', $filters['account_ids']);
        }

        // Category filters
        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $query->whereIn('transaction_category_id', $filters['category_ids']);
        }

        // Type filters
        if (!empty($filters['type']) && $filters['type'] !== 'both') {
            $query->where('type', $filters['type']);
        }

        // Flag filters
        if (!empty($filters['flags']) && is_array($filters['flags'])) {
            $query->whereIn('flag', $filters['flags']);
        }

        // Amount filters
        if (!empty($filters['amount_min'])) {
            $query->where('amount', '>=', $filters['amount_min']);
        }

        if (!empty($filters['amount_max'])) {
            $query->where('amount', '<=', $filters['amount_max']);
        }

        // Tag filters
        if (!empty($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            $query->whereHas('tags', function (Builder $q) use ($filters) {
                $q->whereIn('tags.id', $filters['tag_ids']);
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhereHas('account', function (Builder $q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function (Builder $q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting(Builder $query, array $filters): void
    {
        $sortBy = $filters['sort_by'] ?? 'date';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'amount':
                $query->orderBy('amount', $sortOrder);
                break;
            case 'account':
                $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                      ->orderBy('accounts.name', $sortOrder)
                      ->select('transactions.*');
                break;
            case 'category':
                $query->join('transaction_categories', 'transactions.transaction_category_id', '=', 'transaction_categories.id')
                      ->orderBy('transaction_categories.name', $sortOrder)
                      ->select('transactions.*');
                break;
            default: // date
                $query->orderBy('date', $sortOrder)
                      ->orderBy('created_at', $sortOrder);
                break;
        }
    }

    /**
     * Apply date preset filters
     */
    private function applyDatePreset(Builder $query, string $preset): void
    {
        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $query->whereDate('date', $now->toDateString());
                break;
            case 'week':
                $query->whereBetween('date', [
                    $now->copy()->startOfWeek()->toDateString(),
                    $now->copy()->endOfWeek()->toDateString()
                ]);
                break;
            case 'month':
                $query->whereBetween('date', [
                    $now->copy()->startOfMonth()->toDateString(),
                    $now->copy()->endOfMonth()->toDateString()
                ]);
                break;
            case 'year':
                $query->whereBetween('date', [
                    $now->copy()->startOfYear()->toDateString(),
                    $now->copy()->endOfYear()->toDateString()
                ]);
                break;
        }
    }

    /**
     * Get period label for display
     */
    private function getPeriodLabel(array $filters): string
    {
        if (!empty($filters['date_preset'])) {
            switch ($filters['date_preset']) {
                case 'today':
                    return 'Hari Ini';
                case 'week':
                    return 'Minggu Ini';
                case 'month':
                    return Carbon::now()->format('F Y');
                case 'year':
                    return Carbon::now()->format('Y');
            }
        }

        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $from = $filters['date_from'] ? Carbon::parse($filters['date_from'])->format('d M Y') : '';
            $to = $filters['date_to'] ? Carbon::parse($filters['date_to'])->format('d M Y') : '';
            
            if ($from && $to) {
                return "{$from} - {$to}";
            } elseif ($from) {
                return "Sejak {$from}";
            } elseif ($to) {
                return "Sampai {$to}";
            }
        }

        return 'Semua Transaksi';
    }

    /**
     * Get stats for specific period
     */
    private function getStatsForPeriod(User $user, Carbon $from, Carbon $to): array
    {
        $query = Transaction::where('user_id', $user->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        $summary = $query->selectRaw('
            SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expense
        ')->first();

        return [
            'income' => (float) $summary->total_income,
            'expense' => (float) $summary->total_expense,
        ];
    }
}
