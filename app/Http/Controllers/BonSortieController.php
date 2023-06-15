<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\bonSortie;
use App\Models\bonSortieArticle;
use App\Models\Camion;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Secteur;
use App\Models\Vendeur;
use App\Models\venteSecteur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonSortieController extends Controller
{
    public function index()
    {

        try {

            $bonSorties = bonSortie::join('secteurs', 'bon_sorties.secteur_id', '=', 'secteurs.id')
              ->join('warehouses', 'bon_sorties.warehouse_id', '=', 'warehouses.id')
              ->join('camions', 'bon_sorties.camion_id', '=', 'camions.id')
              ->join('vendeurs as vendeur1', 'bon_sorties.vendeur_id', '=', 'vendeur1.id')
              ->leftJoin('vendeurs as vendeur2', 'bon_sorties.aideVendeur_id', '=', 'vendeur2.id')
              ->leftJoin('vendeurs as vendeur3', 'bon_sorties.aideVendeur2_id', '=', 'vendeur3.id')
              ->leftjoin('vente_secteurs', 'bon_sorties.id', '=', 'vente_secteurs.bonSortie_id')
              ->select(
                  'bon_sorties.*',
                  'vente_secteurs.id as vente_secteur_id',
                  'warehouses.nom_Warehouse',
                  'secteurs.secteur',
                  'camions.matricule',
                  'camions.marque',
                  'camions.modele',
                  'vendeur1.nomComplet as nomComplet1',
                  'vendeur2.nomComplet as nomComplet2',
                  'vendeur3.nomComplet as nomComplet3'
              )
              ->get();

            return  response()->json($bonSorties);

        } catch(Exception $e) {

            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);

        }

    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'reference' => 'required',
                'vendeur_id' => 'required',
                'camion_id' => 'required',
                'secteur_id' => 'required',
                'camionKM'=> 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = bonSortie::where('reference', $request->reference)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'ce Bon Sortie est déjà exists'
                ], 409);
            }

            $camion = Camion::find($request->camion_id);
            if (!$camion) {
                return response()->json([
                    'message' => 'ce Camion est introuvable'
                ], 409);
            }

            $secteur = Secteur::find($request->secteur_id);
            if (!$secteur) {
                return response()->json([
                    'message' => 'Le Secteur est introuvable'
                ], 409);
            }

            foreach($request->Articles as $article) {

                $CheckStock = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $secteur->warehouseDistrubtion_id)
                ->first();

                $produit = Article::where('id', $article['article_id'])->first();
                if(!$CheckStock) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,

                    ], 404);
                }

                if($article['QuantitySortie'] > $CheckStock->actual_stock) {
                    DB::rollBack();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'Quantity'=> $article['QuantitySortie'],
                     'actual_stock' =>$CheckStock->actual_stock
                  ], 404);
                }
            }


            // Create the new Bon Commande
            $Added = bonSortie::create([
                'reference' => $request->reference,
                'vendeur_id' => $request->vendeur_id,
                'aideVendeur_id' => $request->aideVendeur_id,
                'aideVendeur2_id' => $request->aideVendeur2_id,
                'camion_id' => $request->camion_id,
                'Commentaire' => $request->Commentaire,
                'dateSortie' => Carbon::now(),
                'camionKM' => $request->camionKM,
                'Confirme' => 0,
                'warehouse_id' => $secteur->warehouseDistrubtion_id,
                'secteur_id' => $request->secteur_id,
            ]);

            // Check if the Bon Commande was successfully created
            if (!$Added) {

                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
                ], 400);
            }

            // Add the bon Articles Related to bon Commande
            foreach($request->Articles as $article) {

                if($article['QuantitySortie'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                bonSortieArticle::create([
                   'article_id' => $article['article_id'],
                   'QuantitySortie' => $article['QuantitySortie'],
                   'bonSorties_id' => $Added->id
               ]);
            }

            DB::commit();

            // Return a success message and the new Bon Commande ID
            return response()->json([
                    'message' => 'Création réussie de Bon Sortie',
                    'id' => $Added->id
                ]);


        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    }

    public function getNumeroBS()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = bonSortie::latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->reference, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->reference, 2, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'S-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['num_bs' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function CheckVendeurCredit($id)
    {
        try {
            $Vendeur = Vendeur::find($id);
            if (!$Vendeur) {
                return response()->json([
                    'message' => 'Le client est introuvable'
                ], 404);
            }

            $TotalCredit = venteSecteur::where(function ($query) use ($Vendeur) {
                $query->where('vendeur_id', $Vendeur->id)
                    ->orWhere('aideVendeur_id', $Vendeur->id)
                    ->orWhere('aideVendeur2_id', $Vendeur->id);
            })->sum('Total_Rester');

            if($TotalCredit > 0) {
                return response()->json([
                    'message' => 'Le Vendeur '.$Vendeur->nomComplet.' a un crédit de '.$TotalCredit.'Dhs'
                ], 200);
            }

        } catch(Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function markAsConfirmed($id)
    {

        DB::beginTransaction();
        try {
            $bonSortie = bonSortie::find($id);

            if(!$bonSortie) {
                return response()->json([
                    'message' => 'Bon de Sortie introuvable'
                ], 400);
            }

            if($bonSortie->Confirme == true) {
                return response()->json([
                    'message' => 'Bon de Sortie est déjà Confirmé'
                ], 400);
            }

            $Articles = bonSortieArticle::where('bonSorties_id', $bonSortie->id)->get();

            foreach($Articles as $article) {

                $CheckStock = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $bonSortie->warehouse_id)
                ->first();

                if(!$CheckStock) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt'
                    ], 404);
                }



                if($article['QuantitySortie'] > $CheckStock->actual_stock) {
                    DB::rollBack();
                    $produit = Article::where('id', $article['article_id'])->first();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'QuantitySortie'=> $article['QuantitySortie'],
                     'actual_stock' =>$CheckStock->actual_stock
                    ], 404);
                }

                $CheckStock->update([
                 'actual_stock' => $CheckStock->actual_stock - $article['QuantitySortie'],
                ]);



            }


            $bonSortie->update([
                'Confirme' => true,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Le bon sortie de Vente se confirme avec succès , et le stock a ete mise ajoure avec succes',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }

    }


    public function show($id)
    {
        try {

            $bonSorties = bonSortie::find($id);
            if(!$bonSorties) {
                return response()->json([
                    'message' => 'bon de Sortie introuvable'
                ], 404);
            }

            $detailsCommande = bonSortieArticle::where('bonSorties_id', $bonSorties->id)->get();

            $articles = [];

            foreach($detailsCommande as $detail) {

                $articl = Article::withTrashed()->where('id', $detail->article_id)->first();
                $article = [
                    'article_id' => $detail->article_id,
                    'QuantitySortie' => $detail->QuantitySortie,
                    'reference' => $articl->reference,
                    'article_libelle' => $articl->article_libelle,
                    'unite' => $articl->unite,


                ];
                $articles[] = $article;
            }

            $bonSorties =  bonSortie::join('secteurs', 'bon_sorties.secteur_id', '=', 'secteurs.id')
            ->join('warehouses', 'bon_sorties.warehouse_id', '=', 'warehouses.id')
            ->join('camions', 'bon_sorties.camion_id', '=', 'camions.id')
            ->join('vendeurs as vendeur1', 'bon_sorties.vendeur_id', '=', 'vendeur1.id')
            ->leftjoin('vendeurs as vendeur2', 'bon_sorties.aideVendeur_id', '=', 'vendeur2.id')
            ->leftjoin('vendeurs as vendeur3', 'bon_sorties.aideVendeur2_id', '=', 'vendeur3.id')
            ->leftjoin('vente_secteurs', 'bon_sorties.id', '=', 'vente_secteurs.bonSortie_id')
            ->select(
                'bon_sorties.*',
                'vente_secteurs.id as vente_secteur_id',
                'secteurs.secteur',
                'warehouses.nom_Warehouse',
                'camions.matricule',
                'camions.marque',
                'camions.modele',
                'vendeur1.nomComplet as nomComplet1',
                'vendeur2.nomComplet as nomComplet2',
                'vendeur3.nomComplet as nomComplet3'
            )
            ->where('bon_sorties.id', $id)
            ->first();

            if (!$bonSorties) {
                return response()->json([
                    'message' => 'Bon Sortie not found'
                ], 400);
            }

            $bonSortiesArray = $bonSorties->toArray();
            $bonSortiesArray['Articles'] = $articles;

            return response()->json($bonSortiesArray);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }



    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $bonSortie_Founded = bonSortie::find($id);


            if (!$bonSortie_Founded) {
                return response()->json([
                    'message' => 'Bon Sortie introuvable'
                ], 404);
            }

            if($bonSortie_Founded->Confirme == true) {
                return response()->json([
                    'message' => 'Bon Sortie est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonSortieArticle::where('bonSorties_id', $bonSortie_Founded->id)->delete();
            $bonSortie_Founded->delete();

            DB::commit();
            return response()->json([
                'message' => 'Bon Sortie nest plus disponible',
                'id' => $bonSortie_Founded->id

            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function printbs($id, $isDownloaded)
    {
        try {

            $commande = bonSortie::join('warehouses', 'bon_sorties.warehouse_id', '=', 'warehouses.id')
            ->select('bon_sorties.*', 'warehouses.nom_Warehouse')
            ->find($id);

            if (!$commande) {
                return response()->json([
                    'message' => 'Ce Bon Sortie est introuvable'
                ], 409);
            }

            $articles = bonSortieArticle::select('bon_sortie_articles.*', 'articles.reference', 'articles.article_libelle', 'articles.unite')
            ->join('articles', 'bon_sortie_articles.article_id', '=', 'articles.id')
            ->where('bonSorties_id', $commande->id)
            ->get();

            $vendeur1 = Vendeur::where('id', $commande->vendeur_id)->first();
            $vendeur2 = Vendeur::where('id', $commande->aideVendeur_id)->first();
            $vendeur3 = Vendeur::where('id', $commande->aideVendeur2_id)->first();

            $company = Company::get()->first();

            $secteur = Secteur::where('id', $commande->secteur_id) ->first();
            $dateTirage = Carbon::now();

            $camion = camion::where('id', $commande->camion_id)->first();
            $pdf = app('dompdf.wrapper');

            //############ Permitir ver imagenes si falla ################################
            $contxt = stream_context_create([
              'ssl' => [
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true,
              ]
            ]);
            $pdf->setPaper('A4', 'landscape');
            $pdf->getDomPDF()->setHttpContext($contxt);


            $pdf->loadView('Prints.vente.bonSortie', compact('commande', 'articles', 'company', 'pdf', 'vendeur1', 'vendeur2', 'vendeur3', 'secteur', 'dateTirage', 'camion'));




            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Sortie_Nº'.$commande->reference.'.pdf');
            }

            return $pdf->stream('Bon_Sortie_Nº'.$commande->reference.'.pdf');


        } catch (Exception $e) {

            abort(404);
        }

    }
}
