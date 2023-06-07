<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class employee extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'employees';
    protected $fillable = [
        'nom_employee',
        'code_employee',
        'CIN_employee',
        'matricule_employee',
        'telephone_employee',
        'email_employee',
        'adresse_employee',
        'date_embauche',
        'role_id',
    ];
  public function role()
  {
      return $this->belongsTo(employeeRole::class);
  }

}
