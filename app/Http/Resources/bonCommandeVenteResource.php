<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class bonCommandeVenteResource extends JsonResource
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
            'client' => $this->client->nom_Client,
            'client_id' => $this->client_id,
            'Numero_bonCommandeVente' => $this->Numero_bonCommandeVente,
            'Exercice' => $this->Exercice,
            'Mois' => $this->Mois,
            'Etat' => $this->Etat,
            'Commentaire' => $this->Commentaire,
            'date_BCommandeVente' => $this->date_BCommandeVente,
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
