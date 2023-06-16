<?php

namespace App\Http\Controllers;

use App\Events\AlertStockProcessed;
use App\Http\Resources\bonLivraisonVenteResource;
use App\Models\Article;
use App\Models\BankAccount;
use App\Models\bonCommandeVente;
use App\Models\bonLivraison;
use App\Models\bonLivraisonArticle;
use App\Models\bonLivraisonVente;
use App\Models\bonLivraisonVenteArticle;
use App\Models\client;
use App\Models\Company;
use App\Models\facture;
use App\Models\Fournisseur;
use App\Models\Inventory;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonLivraisonVenteController extends Controller
{
    public function index()
    {
        try {

            $bonLivraison = bonLivraisonVente::leftjoin('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
            ->leftjoin('clients', 'bon_livraison_ventes.client_id', '=', 'clients.id')
            ->leftjoin('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
            ->leftjoin('bonretour_ventes', 'bonretour_ventes.bonLivraison_id', 'bon_livraison_ventes.id')

            ->select(
                'bon_livraison_ventes.*',
                'bon_commande_ventes.Numero_bonCommandeVente',
                'bon_commande_ventes.id as bonCommandeVente_id',
                'warehouses.nom_Warehouse',
                'warehouses.id as warehouse_id',
                'clients.nom_Client',
                'bonretour_ventes.id as bonretour_id',
                'bonretour_ventes.Numero_bonRetour'
            )
            ->get();
            return  response()->json(['data'=>$bonLivraison]);

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
             'Numero_bonLivraisonVente' => 'required',
             'bonCommandeVente_id' => 'required',
             'client_id' => 'required',
             'Confirme' => 'required',
             'warehouse_id' => 'required',
             'Total_HT' => 'required',
             'Total_TTC' => 'required'
             ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()
                ], 400);
            }

            // Check if the Bon Livraison already exists
            $found = bonLivraisonVente::where('Numero_bonLivraisonVente', $request->Numero_bonLivraisonVente)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Le Bon de Livraison ne peut pas être dupliqué'
                ], 400);
            }




            foreach($request->Articles as $article) {

                $CheckStock = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
                $produit = Article::where('id', $article['article_id'])->first();
                if(!$CheckStock) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,

                    ], 404);
                }



                if($article['Quantity'] > $CheckStock->actual_stock) {
                    DB::rollBack();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'Quantity'=> $article['Quantity'],
                     'actual_stock' =>$CheckStock->actual_stock
                  ], 404);
                }
            }


            // Check if the Bon Livraison is Filled or not
            if($request->bonCommandeVente_id) {
                $bonCommandeVente = bonCommandeVente::where('id', $request->bonCommandeVente_id)->exists();
                // Check if the Bon Livraison filled is existe
                if(!$bonCommandeVente) {
                    return response()->json([
                        'message' => 'Le Bon de Commande introuvable'
                    ], 400);
                }
            }


            // Parse Date to get Month and years of the Bon Reception
            $date = Carbon::parse($request->date_BlivraisonVente);
            // if the Commande is Confirmed then Status of the commande is Recu Otherwise is Saisi
            if($request->Confirme == true) {
                $Etat = 'Recu';

            } else {
                $Etat = 'saisi';
            }

            // Create the new Bon Livraison
            $Added = bonLivraisonVente::create([
                'Numero_bonLivraisonVente' => $request->Numero_bonLivraisonVente,
                'bonCommandeVente_id' => $request->bonCommandeVente_id,
                'client_id' => $request->client_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('m'),
                'Etat' => $Etat,
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_BlivraisonVente' => $request->date_BlivraisonVente,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
                'warehouse_id' => $request->warehouse_id,
                'camion_id' => $request->camion_id,
                'transporteur_id' => $request->transporteur_id
            ]);

            // Check if the Bon Commande was successfully created
            if (!$Added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            if(!$request->Articles) {
                DB::rollBack();
                return response()->json([
                'message' => 'Aucun article sur le bon de livraison,  il sera annulé'
                ], 404);
            }

            // Add the bon Articles Related to bon Commande
            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                bonLivraisonVenteArticle::create([
                   'blVente_id' => $Added->id,
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
              ]);


                if($request->Confirme == true) {

                    $CheckStock = Inventory::where('article_id', $article['article_id'])
                    ->where('warehouse_id', $request->warehouse_id)
                    ->first();

                    if(!$CheckStock) {
                        DB::rollBack();
                        return response()->json([
                           'message' => 'le produit est introuvable dans cet entrepôt',
                        ], 404);
                    }

                    $produit = Article::where('id', $article['article_id'])->first();

                    if($article['Quantity'] > $CheckStock->actual_stock) {
                        DB::rollBack();
                        return response()->json([
                         'message' => 'La Quantité requise non disponible a ce moment pour larticle '.$produit->reference.'::'.$produit->article_libelle,
                         'Quantity'=> $article['Quantity'],
                         'actual_stock' =>$CheckStock->actual_stock
             ], 404);
                    }

                    $CheckStock->update([
                     'actual_stock' => $CheckStock->actual_stock - $article['Quantity'],
                      ]);
                }

                DB::commit();

            }

            DB::commit();

            return response()->json([
                    'message' => 'Création réussie de Bon Livraison',
                    'id' => $Added->id
                ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function markConfirmed($id)
    {
        DB::beginTransaction();
        try {
            $bonLivraison = bonLivraisonVente::find($id);

            if(!$bonLivraison) {
                return response()->json([
                    'message' => 'Bon de Livraison de vente introuvable'
                ], 400);
            }

            if($bonLivraison->Confirme == true) {
                return response()->json([
                    'message' => 'Bon de Livraison de vente est déjà Confirmé'
                ], 400);
            }



            $Articles =  bonLivraisonVenteArticle::where('blVente_id', $id)->get();
            $isAlerted = false;
            foreach($Articles as $article) {

                $CheckStock = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $bonLivraison->warehouse_id)
                ->first();

                if(!$CheckStock) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt'
                    ], 404);
                }

                $produit = Article::where('id', $article['article_id'])->first();
                $isAlerted = false;
                if($article['Quantity'] > $CheckStock->actual_stock) {
                    DB::rollBack();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'Quantity'=> $article['Quantity'],
                     'actual_stock' =>$CheckStock->actual_stock
                  ], 404);
                }

                $CheckStock->update([
                 'actual_stock' => $CheckStock->actual_stock - $article['Quantity'],
                ]);

                if($produit->alert_stock >= $CheckStock->actual_stock) {
                    $isAlerted = true;
                }

            }

            $bonLivraison->update([
               'Confirme' => true,
               'Etat' => 'Recu',
                ]);

            DB::commit();

            if($isAlerted) {
                event(new AlertStockProcessed());
            }

            return response()->json([
                'message' => 'Le bon Livraison de Vente se confirme avec succès , et le stock a ete mise ajoure avec succes'
            ], 200);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 404);
        }

    }


    public function show($id)
    {
        try {

            $bonLivraison = bonLivraisonVente::find($id);
            if(!$bonLivraison) {
                return response()->json([
                    'message' => 'le bon de Livraison introuvable'
                ], 404);
            }

            $detailsCommande =  bonLivraisonVenteArticle::where('blVente_id', $bonLivraison->id)->get();


            $articles = [];

            foreach($detailsCommande as $detail) {

                $articl = Article::withTrashed()->where('id', $detail->article_id)->first();
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


            //  $bonLivraisonArray = $bonLivraison->toArray();

            $bonLivraison = bonLivraisonVente::join('clients', 'bon_livraison_ventes.client_id', '=', 'clients.id')->withTrashed()
            ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
            ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')->withTrashed()
            ->leftjoin('facture_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
            ->leftjoin('bonretour_ventes', 'bonretour_ventes.bonLivraison_id', 'bon_livraison_ventes.id')

            ->select(
                'bon_livraison_ventes.*',
                'warehouses.nom_Warehouse',
                'bon_commande_ventes.Numero_bonCommandeVente',
                'clients.nom_Client',
                'facture_ventes.id as factureVente_id',
                'bonretour_ventes.id as bonretour_id',
                'bonretour_ventes.Numero_bonRetour'
            )
            ->where('bon_livraison_ventes.id', $id)
            ->first();

            $bonLivraisonArray = $bonLivraison->toArray();
            $bonLivraisonArray['Articles'] = $articles;

            return response()->json($bonLivraisonArray);


        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function destroy(bonLivraisonVente $bonLivraisonVente, $id)
    {
        DB::beginTransaction();
        try {

            $BLivraison_Founded = bonLivraisonVente::find($id);


            if (!$BLivraison_Founded) {
                return response()->json([
                    'message' => 'Bon Livraison de vente introuvable'
                ], 404);
            }

            if($BLivraison_Founded->Confirme == true) {

                return response()->json([
                    'message' => 'Bon Livraison Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonLivraisonVenteArticle::where('blVente_id', $BLivraison_Founded->id)->delete();
            $BLivraison_Founded->delete();




            DB::commit();
            return response()->json([
                'message' => 'Le Bon Livraison de Vente nest plus disponible',
                'id' => $BLivraison_Founded->id

            ]);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function getBonCommandeVente()
    {
        try {

            $linkedBonCommandesVente = bonLivraisonVente::pluck('bonCommandeVente_id')->toArray();
            $bonCommandesVente = bonCommandeVente::where('Confirme', 1)
                            ->whereNotIn('id', $linkedBonCommandesVente)
                            ->get();

            return response()->json($bonCommandesVente);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }
public function getNumeroBLV()
{
    try {
        $year = Carbon::now()->format('Y');

        $lastRecord = bonLivraisonVente::latest()->first();

        if (!empty($lastRecord)) {
            $lastIncrementStringYear = substr($lastRecord->Numero_bonLivraisonVente, -4, 4);
            if ($lastIncrementStringYear === $year) {
                $lastIncrementString = substr($lastRecord->Numero_bonLivraisonVente, 4, 6);
                $incrementNumber = intval($lastIncrementString) + 1;
            } else {
                $incrementNumber = 1;
            }
        } else {
            $incrementNumber = 1;
        }

        $incrementString = 'L-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

        return response()->json(['num_blv' => $incrementString]);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
        ], 404);
    }
}



    public function printbonLivraisonVente($id, $isDownloaded)
    {
        try {


            $commande = bonLivraisonVente::join('clients', 'bon_livraison_ventes.client_id', '=', 'clients.id')
                ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
                ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
                ->select('bon_livraison_ventes.*', 'clients.nom_Client', 'warehouses.nom_Warehouse', 'bon_commande_ventes.Numero_bonCommandeVente')
                ->where('bon_livraison_ventes.id', $id)
                ->first();



            $articles = bonLivraisonVenteArticle::select('bon_livraison_vente_articles.*', 'articles.*')
                ->join('articles', 'bon_livraison_vente_articles.article_id', '=', 'articles.id')
                ->where('blVente_id', $id)
                ->get();

            $bank = BankAccount::get()->first();
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



            $pdf->loadView('Prints.vente.bonLivraisonVente', compact('commande', 'articles', 'client', 'bank', 'company', 'pdf'));



            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Livriason_'.$commande->Numero_bonLivraisonVente.'.pdf');
            }

            return $pdf->stream('Bon_Livriason_'.$commande->Numero_bonLivraisonVente.'.pdf');



        } catch (Exception $e) {
            abort(404);

        }
    }

    public function printbonReceptionVente($id, $isDownloaded)
    {
        try {


            $commande = bonLivraisonVente::join('clients', 'bon_livraison_ventes.client_id', '=', 'clients.id')
            ->join('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
            ->join('warehouses', 'bon_livraison_ventes.warehouse_id', '=', 'warehouses.id')
            ->select('bon_livraison_ventes.*', 'clients.nom_Client', 'warehouses.nom_Warehouse', 'bon_commande_ventes.Numero_bonCommandeVente')
            ->where('bon_livraison_ventes.id', $id)
            ->first();



            $articles = bonLivraisonVenteArticle::select('bon_livraison_vente_articles.*', 'articles.*')
                ->join('articles', 'bon_livraison_vente_articles.article_id', '=', 'articles.id')
                ->where('blVente_id', $id)
                ->get();

            $bank = BankAccount::get()->first();
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



            $pdf->loadView('Prints.vente.bonReceptionVente', compact('commande', 'articles', 'client', 'bank', 'company', 'pdf'));



            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Reception_de_'.$commande->Numero_bonLivraisonVente.'.pdf');
            }

            return $pdf->stream('Bon_reception_de_'.$commande->Numero_bonLivraisonVente.'.pdf');



        } catch (Exception $e) {
            abort(404);

        }
    }
}
