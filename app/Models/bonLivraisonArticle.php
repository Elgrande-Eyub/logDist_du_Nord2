<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonLivraisonArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bon_livraison_articles';
    protected $fillable = [
        'bonLivraison_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];
}
