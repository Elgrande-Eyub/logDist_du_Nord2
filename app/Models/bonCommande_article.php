<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonCommande_article extends Model
{
    use HasFactory;

    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bon_commande_articles';
    protected $fillable = [
        'bonCommande_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];

     public function bonCommande_id()
     {
         return $this->belongsTo(bonCommande::class);
     }
     public function Article()
     {
     return $this->belongsTo(Article::class);
     }

}
