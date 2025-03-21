<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionTag extends Model
{
    use HasFactory;

    protected $table = 'transaction_tag';
    public $timestamps = false;

    protected $fillable = ['transaction_id', 'tag_id'];
}

