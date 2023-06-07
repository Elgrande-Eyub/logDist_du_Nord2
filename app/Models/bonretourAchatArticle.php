<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class bonretourAchatArticle extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'bonretour_achat_articles';
    protected $fillable = [
        'bonretourAchat_id',
        'article_id',
        'Prix_unitaire',
        'Quantity',
        'Total_HT',
    ];
}
