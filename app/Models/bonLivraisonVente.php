<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonLivraisonVente extends Model
{
    use HasFactory;

    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bon_livraison_ventes';
    protected $fillable = [
        'client_id',
        'bonCommandeVente_id',
        'Numero_bonLivraisonVente',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_BlivraisonVente',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
        'TVA',
        'remise',
        'warehouse_id',
    ];


    public function client()
    {
        return $this->belongsTo(client::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(warehouse::class);
    }
    public function bonCommandeVente()
    {
        return $this->belongsTo(bonCommandeVente::class);
    }


    public function Articles()
    {
        return $this->hasMany(Article::class);
    }

    public function Facture()
    {
        return $this->hasOne(factureVente::class);
    }
}
