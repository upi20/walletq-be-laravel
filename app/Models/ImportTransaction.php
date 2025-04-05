<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ImportTransaction extends Model
{
    use HasFactory;

    protected $table = 'import_transactions';

    protected $fillable = [
		'file',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'import_id', 'id');
    }

    public function fileUrl(){
        $path = $this->attributes['file'];
        return Storage::url($path);
    }
}
