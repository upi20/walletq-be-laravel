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
        
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        return Transaction::with(['account', 'category', 'tags'])
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMonthlyTransactionsGroupedByDate(User $user, string $month = null)
    {
        $transactions = $this->getMonthlyTransactions($user, $month);
        $transactionGroupedByDate = $transactions->groupBy(function($transaction) {
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
            $data->date = $date;

            // amount
            $data->amount = 0;
            foreach ($transactions as $transaction) {
                $data->amount += ( $transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
            }

            $data->transactions = $transactions;
            $result[] = $data;
        }
        return $result;
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