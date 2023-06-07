<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonSortie extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;
    protected $table = 'bon_sorties';
    protected $fillable = [
        'reference',
        'dateSortie',
        'vendeur_id',
        'aideVendeur_id',
        'aideVendeur2_id',
        'camion_id',
        'camionKM',
        'secteur_id',
        'Confirme',
        'warehouse_id',
        'Commentaire'
    ];
}
