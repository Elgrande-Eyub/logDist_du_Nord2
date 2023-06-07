<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'inventories';
    protected $fillable = [
        'article_id',
        'warehouse_id',
        'actual_stock',
    ];


    public function Article()
    {
        return $this->belongsTo(Article::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(warehouse::class);
    }
}
