<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transfert extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'transferts';
    protected $fillable = [
        'from',
        'to',
        'Quantity',
        'reference',
        'camion_id',
        'transporteur_id',
        'dateTransfert',
        'Confirme',
        'Commentaire',
    ];
}
