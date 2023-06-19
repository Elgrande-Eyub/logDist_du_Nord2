<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevisArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'devis_articles';
    protected $fillable = [
        'devis_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];
}
