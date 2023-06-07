<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class bonCommandeResource extends JsonResource
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
            'fournisseur' => $this->fournisseur->fournisseur,
            'fournisseur_id' => $this->fournisseur->id,
            'Numero_bonCommande' => $this->Numero_bonCommande,
            'Exercice' => $this->Exercice,
            'Mois' => $this->Mois,
            'Etat' => $this->Etat,
            'Commentaire' => $this->Commentaire,
            'date_BCommande' => $this->date_BCommande,
            'Confirme' => $this->Confirme,
            'Total_HT' => $this->Total_HT,
            'remise' => $this->remise,
            'TVA' => $this->TVA,
            'Total_TVA' => $this->Total_TVA,
            'Total_TTC' => $this->Total_TTC,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
