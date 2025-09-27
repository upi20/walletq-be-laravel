<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

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
        'flag',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    protected $appends = ['flag_label'];

    const CATEGORY_INITIAL_BALANCE = 'Saldo Awal';
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    // Flag ==========================================
    const FLAG_NORMAL = 'normal';
    const FLAG_TRANSFER_IN = 'transfer_in';
    const FLAG_TRANSFER_OUT = 'transfer_out';
    const FLAG_DEBT_PAYMENT = 'debt_payment';
    const FLAG_DEBT_COLLECT = 'debt_collect';
    const FLAG_INITIAL_BALANCE = 'initial_balance';

    const FLAGS = [
        self::FLAG_NORMAL,
        self::FLAG_TRANSFER_IN,
        self::FLAG_TRANSFER_OUT,
        self::FLAG_DEBT_PAYMENT,
        self::FLAG_DEBT_COLLECT,
        self::FLAG_INITIAL_BALANCE,
    ];
    // Flag ==========================================

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

    /**
     * Get the formatted flag label
     */
    public function getFlagLabelAttribute(): string
    {
        return match($this->flag) {
            self::FLAG_NORMAL => 'Normal',
            self::FLAG_TRANSFER_IN => 'Transfer Masuk',
            self::FLAG_TRANSFER_OUT => 'Transfer Keluar', 
            self::FLAG_DEBT_PAYMENT => 'Pembayaran Hutang',
            self::FLAG_DEBT_COLLECT => 'Penagihan Piutang',
            self::FLAG_INITIAL_BALANCE => 'Saldo Awal',
            default => ucfirst(str_replace('_', ' ', $this->flag))
        };
    }
}
