<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonCommande extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bon_commandes';
    protected $fillable = [
        'fournisseur_id',
        'Numero_bonCommande',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_BCommande',
        'Confirme',
        'TVA',
        'remise',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
    ];

    public function bonCommandeArticles()
    {
        return $this->hasMany(bonCommande_article::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function Fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function bonLivraison()
    {
        return $this->hasOne(bonLivraison::class);
    }
}
