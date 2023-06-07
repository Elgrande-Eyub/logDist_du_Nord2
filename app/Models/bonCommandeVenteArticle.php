<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonCommandeVenteArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'bon_commande_vente_articles';
    protected $fillable = [
        'bcVente_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];

    public function bonCommandeVente()
    {
        return $this->belongsTo(bonCommandeVente::class);
    }
    public function Article()
    {
    return $this->belongsTo(Article::class);
    }
}
