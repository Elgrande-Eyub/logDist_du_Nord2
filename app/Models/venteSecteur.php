<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class venteSecteur extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'vente_secteurs';
    protected $fillable = [
        'reference',
        'dateEntree',
        'kilometrageFait',
        'Confirme',
        'EtatPaiement',
        'Total_HT',
        'TVA',
        'Total_TVA',
        'Total_TTC',
        'bonSortie_id',
        'Total_Regler',
        'Total_Rester',
        'vendeur_id',
        'aideVendeur_id',
        'aideVendeur2_id',
        'camion_id',
        'secteur_id',
        'warehouse_id'
    ];
}
