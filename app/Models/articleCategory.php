<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class articleCategory extends Model
{
    use HasFactory;
    public $timestamps = true;
    use SoftDeletes;
    protected $table = 'article_categories';
    protected $fillable = [
        'category',

    ];

    public function SingleCategory()
    {
        return $this->hasMany(Article::class);
    }


}
