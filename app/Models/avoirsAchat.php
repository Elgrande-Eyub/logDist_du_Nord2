<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class avoirsAchat extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'avoirs_achats';
    protected $fillable = [
        'fournisseur_id',
        'bonretourAchat_id',
        'factureChange_id',
        'numero_avoirsAchat',
        'Exercice',
        'Mois',
        // 'EtatPaiement',
        'Commentaire',
        'date_Facture',
        'Confirme',
        'Total_HT',
        'isLinked',
        'Total_TVA',
        'TVA',
        'remise',
        'Total_TTC',
        // 'Total_Regler',
        // 'Total_Rester',
        'attachement',
        // 'bonretourAchat_id',
        // 'raison',
        // 'conditionPaiement'
    ];
}
