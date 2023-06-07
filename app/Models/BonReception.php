<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BonReception extends Model
{
    use HasFactory;

    use SoftDeletes;
    public $timestamps = true;
    protected $table = 'bon_receptions';
    protected $fillable = [
        'fournisseur_id',
        'bonCommande_id',
        'Numero_bonReception',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_BReception',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
        'attachement'
        // 'Total_Regler',
        // 'Total_Rester',

    ];

    public function bonReception_article()
    {
        return $this->hasMany(bonReception_article::class);
    }

    public function hasManyArticls()
    {
        return $this->hasMany(Article::class);
    }

    public function fournisseur_id()
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function hasBonCommande()
    {
        return $this->belongsTo(bonCommande::class);
    }

}
