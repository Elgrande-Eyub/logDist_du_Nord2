<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Caisse extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'caisses';
    protected $fillable = [
        'solde',
        'commentaire',
        'type',

    ];
}
