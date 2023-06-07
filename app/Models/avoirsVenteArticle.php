<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class avoirsVenteArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'avoirs_vente_articles';
    protected $fillable = [
        'avoirsVente_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];
}
