<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;
    protected $table = 'accounts';
    protected $fillable = [
        'user_id',
        'account_category_id',
        'name',
        'type',
        'note',
        'initial_balance',
        'current_balance',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    /**
     * Update account balance based on a new transaction
     */
    public function updateBalance($amount, $type)
    {
        $this->current_balance += ($type === 'income' ? $amount : -$amount);
        $this->save();

        // Update user's total balance
        User::refreshBalance($this->user_id);

        return $this->current_balance;
    }

    /**
     * Check if account has sufficient balance for an expense
     */
    public function hasSufficientBalance($amount)
    {
        return $this->current_balance >= $amount;
    }

    /**
     * Recalculate current balance from all transactions
     */
    public function recalculateBalance()
    {
        $balance = $this->initial_balance;

        $this->transactions()
            ->where('flag', '!=', Transaction::FLAG_INITIAL_BALANCE)
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->chunk(100, function ($transactions) use (&$balance) {
                foreach ($transactions as $transaction) {
                    $balance += ($transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
                }
            });

        if($this->current_balance != $balance) {
            $this->current_balance = $balance;
            $this->save();
        }

        // Update user's total balance
        User::refreshBalance($this->user_id);

        return $balance;
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Kategori Akun
    public function category()
    {
        return $this->belongsTo(AccountCategory::class, 'account_category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
