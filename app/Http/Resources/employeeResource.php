<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class employeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'Nom' => $this->nom_employee,
            'code_employee' => $this->code_employee,
            'CIN_employee' => $this->CIN_employee,
            'matricule_employee' => $this->matricule_employee,
            'telephone_employee' => $this->telephone_employee,
            'email_employee' => $this->email_employee,
            'adresse_employee' => $this->adresse_employee,
            'date_embauche' => $this->date_embauche,
            'role_name' =>$this->role->role_name,
            'role_id' =>$this->role->id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
