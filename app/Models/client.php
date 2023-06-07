<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class client extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'clients';
    protected $fillable = [
        'nom_Client',
        'code_Client',
        'CIN_Client',
        'ICE_Client',
        'RC_Client',
        'telephone_Client',
        'email_Client',
        'adresse_Client',
        'Pattent_Client',
    ];

    public function bonCommandeVentes(){
        return $this->hasMany(bonCommandeVente::class);
    }
}
