<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Calculate and update user's total balance
     */
    public static function refreshBalance($userId)
    {
        $user = static::findOrFail($userId);
        $totalBalance = $user->accounts()
            ->sum('current_balance');
            
        $user->balance = $totalBalance;
        $user->save();
        
        return $totalBalance;
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function accountCategories()
    {
        return $this->hasMany(AccountCategory::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function syncBalance()
    {
        // sync each account balance
        foreach($this->accounts as $account) {
            $account->recalculateBalance();
        }
        // then update user's total balance
        $totalBalance = $this->accounts()->sum('current_balance');
        if($this->balance != $totalBalance) {
            $this->balance = $totalBalance;
            $this->save();
        }
        return $this->balance;
    }
}
