<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'journals';
    protected $fillable = [
        'Code_journal',
        'type',

    ];

    public function hasManyTransactions()
    {
        return $this->hasMany(Transaction::class);
    }


}
