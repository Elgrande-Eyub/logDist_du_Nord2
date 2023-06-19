<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class avoirsVente extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'avoirs_ventes';
    protected $fillable = [
        'client_id',
        'factureVente_id',
        'numero_avoirsVente',
        'Exercice',
        'Mois',
        // 'EtatPaiement',
        'isLinked',
        'Commentaire',
        'date_avoirs',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'TVA',
        'remise',
        'Total_TTC',
     /*    'Total_Regler',
        'Total_Rester', */
        'attachement',
        // 'bonretourVente_id',
        // 'raison',
    ];
}
