<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devis extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'devis';
    protected $fillable = [
        'client_id',
        'Numero_Devis',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_Devis',
        'Confirme',
        'TVA',
        'remise',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
    ];
}
