<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonLivraisonVenteArticle extends Model
{
    use HasFactory;


    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bon_livraison_vente_articles';
    protected $fillable = [
        'blVente_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];
}
