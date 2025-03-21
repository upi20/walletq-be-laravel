<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';

    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'date',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    // Relasi ke semua transaksi yang terkait dengan transfer ini
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'source');
    }

    public function expenseTransaction()
    {
        return $this->transactions()->where('type', 'expense')->where('flag', 'transfer_out');
    }

    public function incomeTransaction()
    {
        return $this->transactions()->where('type', 'income')->where('flag', 'transfer_in');
    }
}
