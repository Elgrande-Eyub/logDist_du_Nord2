<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonConsignationArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bon_consignation_articles';
    protected $fillable = [
        'bonConsignation_id',
        'reference',
        'article_libelle',
        'Prix_unitaire',
        'Quantity',
        'Total',
    ];
}
