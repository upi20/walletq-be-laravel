<?php

namespace App\Services\Report;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Report Service
 * 
 * NEW SERVICE - Created for Reports feature
 * Does NOT modify any existing services
 */
class ReportService
{
    /**
     * Get income breakdown by category
     * 
     * @param User $user
     * @param array $filters
     * @return array
     */
    public function getIncomeByCategory(User $user, array $filters): array
    {
        // Force type to income
        $filters['type'] = 'income';
        
        return $this->getCategoryBreakdown($user, $filters, 'income');
    }

    /**
     * Get expense breakdown by category
     * 
     * @param User $user
     * @param array $filters
     * @return array
     */
    public function getExpenseByCategory(User $user, array $filters): array
    {
        // Force type to expense
        $filters['type'] = 'expense';
        
        return $this->getCategoryBreakdown($user, $filters, 'expense');
    }

    /**
     * Get category breakdown with filters
     * PRIVATE METHOD - internal use only
     * 
     * @param User $user
     * @param array $filters
     * @param string $type
     * @return array
     */
    private function getCategoryBreakdown(User $user, array $filters, string $type): array
    {
        $query = Transaction::select([
                'transaction_category_id as category_id',
                'transaction_categories.name as category_name',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count')
            ])
            ->join('transaction_categories', 'transactions.transaction_category_id', '=', 'transaction_categories.id')
            ->where('transactions.user_id', $user->id)
            ->where('transactions.type', $type)
            ->groupBy('transaction_category_id', 'transaction_categories.name')
            ->having('total_amount', '>', 0)
            ->orderByDesc('total_amount');

        // Apply filters - COPIED logic from TransactionService, not modifying original
        $this->applyDateFilters($query, $filters);
        $this->applyOtherFilters($query, $filters);

        $data = $query->get();
        $total = $data->sum('total_amount');
        
        return $data->map(function($item) use ($total) {
            return [
                'category_id' => $item->category_id,
                'category_name' => $item->category_name,
                'total_amount' => (float) $item->total_amount,
                'transaction_count' => $item->transaction_count,
                'percentage' => $total > 0 ? round(($item->total_amount / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Apply date filters
     * COPIED from TransactionService - not modifying original
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private function applyDateFilters($query, array $filters): void
    {
        $period = $filters['period'] ?? 'month';
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $month = $filters['month'] ?? null;
        $year = $filters['year'] ?? null;

        if ($period === 'custom' && $dateFrom && $dateTo) {
            $query->whereBetween('transactions.date', [$dateFrom, $dateTo]);
        } elseif ($period === 'today') {
            $query->whereDate('transactions.date', Carbon::today());
        } elseif ($period === 'week') {
            $query->whereBetween('transactions.date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $targetMonth = $month ?: Carbon::now()->format('Y-m');
            $startDate = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $targetMonth)->endOfMonth();
            $query->whereBetween('transactions.date', [$startDate, $endDate]);
        } elseif ($period === 'year') {
            $targetYear = $year ?: Carbon::now()->year;
            $query->whereYear('transactions.date', $targetYear);
        }
        // 'all' period = no date filter
    }

    /**
     * Apply other filters
     * COPIED logic from TransactionService - not modifying original
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private function applyOtherFilters($query, array $filters): void
    {
        // Account filter
        if (!empty($filters['account_ids']) && is_array($filters['account_ids'])) {
            $query->whereIn('transactions.account_id', $filters['account_ids']);
        }

        // Tag filter
        if (!empty($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            $query->whereHas('tags', function($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tag_ids']);
            });
        }

        // Flag filter
        if (!empty($filters['flags']) && is_array($filters['flags'])) {
            $query->whereIn('transactions.flag', $filters['flags']);
        }

        // Amount filter
        if (isset($filters['amount_min'])) {
            $query->where('transactions.amount', '>=', $filters['amount_min']);
        }
        if (isset($filters['amount_max'])) {
            $query->where('transactions.amount', '<=', $filters['amount_max']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('transactions.note', 'like', "%{$filters['search']}%")
                  ->orWhere('transaction_categories.name', 'like', "%{$filters['search']}%");
            });
        }
    }
}
