<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class factureAvoirsachat extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'facture_avoirsachats';
    protected $fillable = [
        'avoirsAchat_id',
        'factureAchat_id',

    ];
}
