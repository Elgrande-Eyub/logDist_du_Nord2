<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Article extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'articles';
    protected $fillable = [
        'fournisseur_id',
        'article_libelle',
        'reference',
        'prix_unitaire',
        'prix_achat',
        'prix_public',
        'client_Fedele',
        'demi_grossiste',
        'unite',
        'category_id',
        'alert_stock',
    ];

    public function category()
    {
        return $this->belongsTo(articleCategory::class);
    }

    public function bonCommandes()
    {
        return $this->hasMany(bonCommande::class);
    }

    public function BonReceptions()
    {
        return $this->hasMany(BonReception::class);
    }
    public function fournisseur()
    {
        return $this->belongsTo(fournisseur::class);
    }


    // public function inventory_id()
    // {
    //     return $this->belongsTo(Inventory::class);
    // }
}
