<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonReception_article extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'bon_reception_articles';
    protected $fillable = [
        'bonReception_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
        '%TVA',
        'Total_TVA',
        'Total_TTC',
    ];


    public function hasManyArticls()
    {
        return $this->hasMany(Article::class);
    }
}
