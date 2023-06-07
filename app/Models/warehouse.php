<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class warehouse extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'warehouses';
    protected $fillable = [
        'nom_Warehouse',
        'city',
        'adresse',
        'telephone',
        'email',
    ];

    public function Inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
