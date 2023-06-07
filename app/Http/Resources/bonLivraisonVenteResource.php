<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class bonLivraisonVenteResource extends JsonResource
{

    public function toArray($request)
    {


        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'client' => $this->client->nom_Client,
            'Numero_bonLivraisonVente' => $this->Numero_bonLivraisonVente,
            'bonCommandeVente_id' => $this->bonCommandeVente_id,
            'Numero_bonCommande' => $this->Numero_bonCommandeVente,
            'Exercice' => $this->Exercice,
            'Mois' => $this->Mois,
            'Etat' => $this->Etat,
            'Commentaire' => $this->Commentaire,
            'date_BlivraisonVente' => $this->date_BlivraisonVente,
            'Confirme' => $this->Confirme,
            'Total_HT' => $this->Total_HT,
            'remise' => $this->remise,
            'TVA' => $this->TVA,
            'Total_TVA' => $this->Total_TVA,
            'Total_TTC' => $this->Total_TTC,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => $this->warehouse->nom_Warehouse,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
