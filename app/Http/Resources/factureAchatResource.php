<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class factureAchatResource extends JsonResource
{

    public function toArray($request)
    {


        return [
            'id' => $this->id,

            'numero_Facture' => $this->numero_Facture,
            'Exercice' => $this->Exercice,
            'Mois' => $this->Mois,
            'Commentaire' => $this->Commentaire,
            'date_Facture' => $this->date_Facture,
            'EtatPaiement' => $this->EtatPaiement,
            'Confirme' => $this->Confirme,
            'Total_HT' => $this->Total_HT,
            'remise' => $this->remise,
            'TVA' => $this->TVA,
            'Total_TVA' => $this->Total_TVA,
            'Total_TTC' => $this->Total_TTC,
            "Total_Regler" => $this->Total_Regler,
            "Total_Rester" => $this->Total_Rester,
            'fournisseur' => $this->fournisseur->fournisseur,
            'fournisseur_id' => $this->fournisseur->id,
            'bonLivraison_id' => $this->bonLivraison_id,
            'Numero_bonLivraison' => $this->Numero_bonLivraison,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
