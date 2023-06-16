<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendeur extends Model
{
    use HasFactory;
    use SoftDeletes;


    public $timestamps = true;

    protected $table = 'vendeurs';
    protected $fillable = [
        'nomComplet',
        'cin',
        'dateEmbauche',
        'dateNaissance',
        'telephone',
        'adresse',
        'isBlacklist'
    ];

}
