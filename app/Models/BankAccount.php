<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BankAccount extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bank_accounts';
    protected $fillable = [
        'nomBank',
        'telephone',
        'adresse',
        'numero_compt',
        'rib_compt',
        'solde',
        'Commentaire',

    ];


    public function hasManyTransactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
