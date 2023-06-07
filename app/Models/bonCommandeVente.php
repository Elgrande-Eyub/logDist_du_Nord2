<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class bonCommandeVente extends Model
{
    use HasFactory;

    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bon_commande_ventes';
    protected $fillable = [
        'client_id',
        'Numero_bonCommandeVente',
        'Exercice',
        'Mois',
        'Etat',
        'Commentaire',
        'date_BCommandeVente',
        'Confirme',
        'TVA',
        'remise',
        'Total_HT',
        'Total_TVA',
        'Total_TTC',
        'attachement'
    ];



    public function client(){
        return $this->belongsTo(client::class);
    }

}
