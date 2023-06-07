<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonSortieArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = 'bon_sortie_articles';
    protected $fillable = [
        'bonSorties_id',
        'article_id',
        'QuantitySortie',

    ];
}
