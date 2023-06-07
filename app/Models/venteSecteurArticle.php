<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class venteSecteurArticle extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'vente_secteur_articles';
    protected $fillable = [
        'venteSecteur_id',
        'article_id',
        'qte_sortie',
        'qte_retourV',
        'qte_perime',
        'qte_echange',
        'qte_gratuit',
        'qte_credit',
        'qte_vendu',
        'Prix_unitaire',
        'Total_Vendu',

    ];
}
