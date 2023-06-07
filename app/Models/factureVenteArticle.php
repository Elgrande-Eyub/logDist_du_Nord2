<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class factureVenteArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'facture_vente_articles';
    protected $fillable = [
        'factureVente_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',

    ];
}
