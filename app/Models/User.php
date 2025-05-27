<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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

    // implement method wajib
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function accountCategories()
    {
        return $this->hasMany(AccountCategory::class, 'user_id', 'id');
    }

    public function transactionCategories()
    {
        return $this->hasMany(TransactionCategory::class, 'user_id', 'id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'user_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'user_id', 'id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'user_id', 'id');
    }

    public static function refreshBalance($user_id)
    {
        try {
            $user = self::find($user_id);
            $amount = 0;
            $allAccount = Account::where('user_id', $user_id)->get();
            foreach ($allAccount as $account) {
                $income = Transaction::where('user_id', $user_id)
                    ->where('account_id', $account->id)
                    ->where('type', 'income')->sum('amount');
                $expense = Transaction::where('user_id', $user_id)
                    ->where('account_id', $account->id)
                    ->where('type', 'expense')->sum('amount');

                $account->current_balance = $income - $expense;
                $account->save();

                $amount += $account->current_balance;
            }

            $user->balance = $amount;
            $user->save();
            return $amount;
        } catch (\Throwable $th) {
            Log::info($th);
        }
    }
}
