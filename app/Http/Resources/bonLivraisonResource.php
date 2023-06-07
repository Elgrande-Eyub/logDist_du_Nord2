<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class bonLivraisonResource extends JsonResource
{

    public function toArray($request)
    {


        return [
            'id' => $this->id,
            'Numero_bonLivraison' => $this->Numero_bonLivraison,
            'fournisseur_id' => $this->fournisseur->id,
            'fournisseur' => $this->fournisseur->fournisseur,
            'bonCommande_id' => $this->bonCommande_id,
            'Numero_bonCommande' => $this->Numero_bonCommande,
            'warehouse_id' => $this->warehouse_id,
            'nom_warehouse' => $this->warehouse->nom_Warehouse,
            'Exercice' => $this->Exercice,
            'Mois' => $this->Mois,
            'Etat' => $this->Etat,
            'Commentaire' => $this->Commentaire,
            'date_Blivraison' => $this->date_Blivraison,
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
