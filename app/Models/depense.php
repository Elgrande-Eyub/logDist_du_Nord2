<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class depense extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'depenses';
    protected $fillable = [

        'depense',
        'depense_Tax',
    ];
}
