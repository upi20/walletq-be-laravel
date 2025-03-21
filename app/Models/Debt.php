<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'debts';

    protected $fillable = [
        'user_id',
        'contact_name',
        'type',
        'total_amount',
        'paid_amount',
        'status',
        'due_date',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'source');
    }
}
