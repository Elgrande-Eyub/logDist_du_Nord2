<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class withdraw extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'withdraws';
    protected $fillable = [
        'type',
        'solde',
        'mode',
        'motif',
        'journal_id',
        'depense_id',
        'attachement'
    ];
}
