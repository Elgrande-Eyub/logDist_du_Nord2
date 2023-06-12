<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BankAccount;
use App\Models\bonLivraisonVente;
use App\Models\client;
use App\Models\Company;
use App\Models\facture_article;
use App\Models\factureVente;
use App\Models\factureVenteArticle;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FactureVenteController extends Controller
{
    public function index()
    {
        try {


            $factures = factureVente::join('clients', 'facture_ventes.client_id', '=', 'clients.id')
            ->join('bon_livraison_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
            ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
            ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
            ->select('facture_ventes.*', 'bon_livraison_ventes.id as bonLivraisonVente_id', 'bon_livraison_ventes.Numero_bonLivraisonVente', 'bon_commande_ventes.Numero_bonCommandeVente', 'bon_commande_ventes.id as bonCommandeVente_id', 'warehouses.nom_Warehouse', 'clients.nom_Client')
            ->get();



            return  response()->json($factures);
        } catch(Exception $e) {


            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);

        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'numero_FactureVente' => 'required',
                'bonLivraisonVente_id' =>'required',
                'client_id' => 'required',
                'Confirme' => 'required',
                'date_FactureVente' => 'required',
                'Total_HT' => 'required',
                'Total_TVA' => 'required',
                'Total_TTC' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }


            $found = factureVente::where('numero_FactureVente', $request->numero_FactureVente)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'La Facture de Vente ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_FactureVente);


            $Added = factureVente::create([
                'numero_FactureVente' => $request->numero_FactureVente,
                'bonLivraisonVente_id' => $request->bonLivraisonVente_id,
                'client_id' => $request->client_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('n'),
                // 'EtatPayment' => $request->EtatPayment, // impaye , en cour , paye
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_FactureVente' =>  Carbon::now(),//$request->date_FactureVente,
                'Total_HT' => $request->Total_HT,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
                'Total_Rester' => $request->Total_TTC,

            ]);

            if (!$Added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            foreach($request->Articles as $article) {

                factureVenteArticle::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
                   'factureVente_id' => $Added->id

               ]);
            }

            DB::commit();

            return response()->json([
               'message' => 'La Facture de Vente créée avec succès',
               'id' => $Added->id
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function show($id)
    {
        try {

            $facture = factureVente::find($id);

            if(!$facture) {
                return   response()->json(['message','Facture not found'], 400);
            }



            $detailsfacture = factureVenteArticle::where('factureVente_id', $facture->id)->get();

            $articles = [];

            foreach($detailsfacture as $detail) {
                $articl= Article::find($detail->article_id);
                $article = [
                    'article_id' => $detail->article_id,
                    'reference' => $articl->reference,
                    'article_libelle' => $articl->article_libelle,
                    'Quantity' => $detail->Quantity,
                    'Prix_unitaire' => $detail->Prix_unitaire,
                    'Total_HT' => $detail->Total_HT,

                ];
                $articles[] = $article;
            }

            $factures = factureVente::join('clients', 'facture_ventes.client_id', '=', 'clients.id')
            ->join('bon_livraison_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
            ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
            ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
            ->select('facture_ventes.*', 'bon_commande_ventes.Numero_bonCommandeVente', 'bon_commande_ventes.id as bonCommandeVente_id', 'bon_livraison_ventes.Numero_bonLivraisonVente', 'warehouses.nom_Warehouse', 'clients.nom_Client')
            ->where('facture_ventes.id', $id)
            ->first();

            $factureArray = $factures;
            $factureArray['Articles'] = $articles;

            return response()->json($factureArray);

        } catch(Exception) {
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // Find the facture  with the given ID
            $factureFounded = factureVente::find($id);

            // If the facture  doesn't exist, return an error
            if (!$factureFounded) {
                return response()->json([
                    'message' => 'facture introuvable'
                ], 404);
            }
            if($factureFounded->Confirme==true) {
                return response()->json([
                    'message' => 'La facture est Confirmé, ne peut pas être supprimé'
                ], 400);
            }
            // Delete the facture
            $factureFounded->delete();
            // Delete all articles related to facture
            factureVenteArticle::where('factureVente_id', $factureFounded->id)->delete();
            Transaction::where('factureVente_id', $factureFounded->id)->delete();
            DB::commit();
            // Return a success message with the deleted Article
            return response()->json([
                'message' => 'La Facture n`est plus disponible',
                'id' => $factureFounded->id
            ]);
        } catch (Exception $e) {
            // Return an error message for any other exceptions
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function markAsConfirmed($id)
    {
        DB::beginTransaction();
        try {
            $facture = factureVente::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'La facture de vente introuvable'
                ], 400);
            }

            $facture->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'La facture de vente se confirmè avec succès',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function markAsPaid($id)
    {
        DB::beginTransaction();
        try {
            $facture = factureVente::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'La facture introuvable'
                ], 400);
            }

            if($facture->Confirme == false) {
                return response()->json([
                    'message' => 'La facture doit être mis en œuvre Confirmé'
                ], 400);
            }

            $facture->update([
                'EtatPaiement' => 'Paye',
                'Total_Regler' => $facture->Total_TTC,
                'Total_Rester'=> 0
            ]);

            DB::commit();
            return response()->json([
                'message' => 'La facture est marquée comme payée',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }
        public function getNumeroFacture()
        {
            try {
                $year = Carbon::now()->format('Y');

                $lastRecord = factureVente::latest()->first();

                if (!empty($lastRecord)) {
                    $lastIncrementStringYear = substr($lastRecord->numero_FactureVente, -4, 4);
                    if ($lastIncrementStringYear === $year) {
                        $lastIncrementString = substr($lastRecord->numero_FactureVente, 3, 5);
                        $incrementNumber = intval($lastIncrementString) + 1;
                    } else {
                        $incrementNumber = 1;
                    }
                } else {
                    $incrementNumber = 1;
                }

                $incrementString = 'F-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

                return response()->json(['num_fv' => $incrementString]);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
                ], 404);
            }
        }

        public function getBonLivraisonVente()
        {
            try {

                $linkedBonLivraisonVente = factureVente::pluck('bonLivraisonVente_id')->toArray();
                $bonLivraisonVente = bonLivraisonVente::where('Confirme', 1)
                                ->whereNotIn('id', $linkedBonLivraisonVente)
                                ->get();

                return response()->json($bonLivraisonVente);
            } catch(Exception $e) {
                DB::rollBack();
                return response()->json([
                   'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 404);
            }

        }

    /* public function GenererFactureV($id)
    {

        DB::beginTransaction();
        $bonLivraisonVente = bonLivraisonVente::find($id);

        if (!$bonLivraisonVente) {
            return response()->json([
                'message' => 'Le Bon Livraison de Vente est introuvable'
            ], 400);
        }

        $bl = app(BonLivraisonVenteController::class);
        $blvResponse = $bl->show($id);

        // Extract the data from the JSON response
        $blvData = $blvResponse->getData();

        // Convert the object to an array
        $blvArray = json_decode(json_encode($blvData), true);

        // Create a new request instance and set the necessary data
        $request = new Request();
        $request->merge($blvArray);
        $request->merge(['bonLivraisonVente_id' => $bonLivraisonVente->id]);
        $request->merge(['numero_FactureVente' => 'FF22']);
        $request->merge(['date_FactureVente' => Carbon::now()]);

        // Pass the request instance to the store() method
        $this->store($request);
        DB::commit();

        return response()->json([
            'message' => 'La facture de vente se ajoute avec succès',
        ]);



    }
 */
    public function facturePrint($id, $isDownloaded)
    {
        try {
            $commande = factureVente::join('bon_livraison_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
             ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
             ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
             ->select('facture_ventes.*', 'bon_livraison_ventes.Numero_bonLivraisonVente', 'bon_livraison_ventes.id as blv_id', 'bon_commande_ventes.Numero_bonCommandeVente', 'warehouses.*')
             ->where('facture_ventes.id', $id)
             ->first();

            $bank = BankAccount::get()->first();




            $articles = factureVenteArticle::select('facture_vente_articles.*', 'articles.*')
              ->join('articles', 'facture_vente_articles.article_id', '=', 'articles.id')
              ->where('factureVente_id', $id)
              ->get();

            $client = client::withTrashed()->find($commande->client_id);

            $company = Company::get()->first();

            $pdf = app('dompdf.wrapper');

            //############ Permitir ver imagenes si falla ################################
            $contxt = stream_context_create([
              'ssl' => [
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true,
              ]
      ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->setHttpContext($contxt);



            $pdf->loadView('Prints.vente.FactureVente', compact('commande', 'articles', 'client', 'bank', 'company', 'pdf'));



            if($isDownloaded === 'true') {
                return $pdf->download('Facture_Nº'.$commande->numero_FactureVente.'.pdf');
            }

            return $pdf->stream('Facture_'.$commande->numero_FactureVente.'.pdf');


        } catch (Exception $e) {
            abort(404);

        }
    }
}
