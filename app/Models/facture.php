<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class facture extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'factures';
    protected $fillable = [
        'fournisseur_id',
        'bonLivraison_id',
        'numero_Facture',
        'Exercice',
        'Mois',
        'EtatPaiement',
        'Commentaire',
        'date_avoirs',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'TVA',
        'remise',
        'Total_TTC',
        'Total_Regler',
        'Total_Rester',
        'attachement',
        'conditionPaiement',
        'isChange',
        'hasAvoirs'
    ];


    public function Bonlivraison()
    {
        return $this->belongsTo(bonLivraison::class);
    }

    public function Fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function Transactions()
    {
        return $this->hasMany(Transaction::class);
    }

}
