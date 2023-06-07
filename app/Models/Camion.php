<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Camion extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'camions';
    protected $fillable = [
        'matricule',
        'marque',
        'modele',
        'annee',
        'etat',
        // 'km',
    ];
}
