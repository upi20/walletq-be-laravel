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
}
