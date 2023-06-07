<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class employeeRole extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'employee_roles';
    protected $fillable = [
        'role_name',
    ];

    public function employees()
    {
        return $this->hasMany(employee::class);
    }
}
