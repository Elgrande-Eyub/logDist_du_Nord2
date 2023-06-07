<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Secteur extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;

    protected $table = 'secteurs';
    protected $fillable = [
        'secteur',
        'warehouseDistrubtion_id',
    ];

    public function warehouse(){

        return $this->belongsTo(warehouse::class);
    }
}
