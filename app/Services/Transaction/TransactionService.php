<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class TransactionService
{
    /**
     * Get transactions for a specific month
     */
    public function getMonthlyTransactions(User $user, string $month = null)
    {
        $month = $month ?: Carbon::now()->format('Y-m');
        
        return Transaction::with(['account', 'category', 'tags'])
            ->where('user_id', $user->id)
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get monthly summary
     */
    public function getMonthlySummary(User $user, string $month = null): array
    {
        $month = $month ?: Carbon::now()->format('Y-m');
        
        $transactions = $this->getMonthlyTransactions($user, $month);
        
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        
        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_amount' => $totalIncome - $totalExpense,
            'transaction_count' => $transactions->count(),
            'month' => $month,
        ];
    }

    /**
     * Get all accounts for user (for filter options)
     */
    public function getUserAccounts(User $user)
    {
        return $user->accounts()->with('category')->get();
    }

    /**
     * Get all transaction categories for user (for filter options)
     */
    public function getUserTransactionCategories(User $user)
    {
        return $user->transactionCategories()->get();
    }

    /**
     * Get all tags for user (for filter options)
     */
    public function getUserTags(User $user)
    {
        return $user->tags()->get();
    }
}