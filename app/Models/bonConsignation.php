<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonConsignation extends Model
{

    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bon_consignations';
    protected $fillable = [
        'facture_id',
        'numero_bonConsignation',
        'Total_Emballages',
        'etat',
        'Commentaire',
        'attachement',
        'representant',
        'transporteur',
        'matriculeCamion',
        'conditionPaiement',
        'Commentaire'
    ];
}
