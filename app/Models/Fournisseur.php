<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'fournisseurs';
    protected $fillable = [
        'code_fournisseur',
        'fournisseur',
        'ICE',
        'IF',
        'RC',
        'Adresse',
        'email',
        'Telephone',
    ];

    public function bonCommandes()
    {
        return $this->hasMany(bonCommande::class);
    }
    public function factures()
    {
        return $this->hasMany(facture::class);
    }

    public function BonReceptions()
    {
        return $this->hasMany(BonReception::class);
    }

    public function Articles()
    {
        return $this->hasMany(Article::class);
    }
}
