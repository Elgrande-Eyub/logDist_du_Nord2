<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonretourVente extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bonretour_ventes';
    protected $fillable = [
        'client_id',
        'bonLivraison_id',
        'Numero_bonRetour',
        'Exercice',
        'Mois',
        'Etat',
        'raison',
        'Commentaire',
        'date_BRetour',
        'Confirme',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
        'TVA',
        'remise',
        'warehouse_id',
        'attachement'

    ];
}
