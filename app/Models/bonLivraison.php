<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonLivraison extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bon_livraisons';
    protected $fillable = [
        'fournisseur_id',
        'bonCommande_id',
        'Numero_bonLivraison',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_Blivraison',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
        'TVA',
        'remise',
        'warehouse_id',
        'attachement'
    ];



    public function Fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(warehouse::class);
    }


    public function Boncommande()
    {
        return $this->belongsTo(bonCommande::class);
    }


    public function Articles()
    {
        return $this->hasMany(Article::class);
    }

    public function Facture()
    {
        return $this->hasOne(Facture::class);
    }
}
