<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class paiementDepense extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'paiement_depenses';
    protected $fillable = [
        'numero_Depense',
        'EtatPaiement',
        'Commentaire',
        'dateDepense',
        'Confirme',
        'montantTotal',
        'TVA',
        'remise',
        'Total_Regler',
        'Total_Rester',
        'depense_id',
        'attachement'
    ];
}
