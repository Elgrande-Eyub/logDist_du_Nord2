<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class factureVente extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'facture_ventes';
    protected $fillable = [
        'client_id',
        'bonLivraisonVente_id',
        'numero_FactureVente',
        'Exercice',
        'Mois',
        'Avance',
        'EtatPaiement',
        'Commentaire',
        'date_FactureVente',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'TVA',
        'remise',
        'Total_TTC',
        'Total_Regler',
        'Total_Rester',
        'conditionPaiement'
    ];

    public function client()
    {
        return $this->belongsTo(client::class);
    }

    public function Transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function bonLivraisonVente()
    {
        return $this->belongsTo(bonLivraisonVente::class);
    }

}
