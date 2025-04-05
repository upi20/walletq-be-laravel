<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'import_id',
        'user_id',
        'account_id',
        'transaction_category_id',
        'type',
        'amount',
        'date',
        'note',
        'source_type',
        'source_id',
        'flag', // ['normal', 'transfer_in', 'transfer_out', 'debt_payment', 'debt_collect', 'initial_balance']
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function import()
    {
        return $this->belongsTo(ImportTransaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }

    // Polymorphic source
    public function source()
    {
        return $this->morphTo();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'transaction_tag');
    }
}
