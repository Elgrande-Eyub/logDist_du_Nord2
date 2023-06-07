<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class articleResource extends JsonResource
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
            'article_libelle' => $this->article_libelle,
            'reference' => $this->reference,
            'prix_unitaire' => $this->prix_unitaire,
            'prix_public' => $this->prix_public,
            'prix_achat' => $this->prix_achat,
            'client_Fedele' => $this->client_Fedele,
            'demi_grossiste' => $this->demi_grossiste,
            'unite' => $this->unite,
            'category' => $this->category->category,
            'category_id' => $this->category->id,
            'fournisseur_id' => $this->fournisseur_id,
            'alert_stock' => $this->alert_stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }


}
