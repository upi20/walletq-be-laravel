<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountCategory extends Model
{
    use HasFactory;

    protected $table = 'account_categories';

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'is_default',
    ];

    // Relasi ke User (optional jika milik user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Akun
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }
}
