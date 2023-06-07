<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class secteurResource extends JsonResource
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
            'secteur' => $this->secteur,
            'warehouseDistrubtion_id' => $this->warehouseDistrubtion_id,
            'nom_Warehouse' => $this->warehouse->nom_Warehouse,
        ];
    }


}
