<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class credit extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'credits';
    protected $fillable = [
        'EtatPaiement',
        'Commentaire',
        'Confirme',
        'montant',
        'Total_Regler',
        'Total_Rester',
        'fournisseur_id',
        'client_id'
    ];
}
