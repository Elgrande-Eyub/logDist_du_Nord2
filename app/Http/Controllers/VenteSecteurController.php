<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\bonSortie;
use App\Models\Camion;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Secteur;
use App\Models\Vendeur;
use App\Models\venteSecteur;
use App\Models\venteSecteurArticle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Random\Engine\Secure;

class VenteSecteurController extends Controller
{
    public function index()
    {
        try {
            $venteSecteur =  venteSecteur::join('warehouses', 'vente_secteurs.warehouse_id', '=', 'warehouses.id')
              ->join('camions', 'vente_secteurs.camion_id', '=', 'camions.id')
              ->join('secteurs', 'vente_secteurs.secteur_id', '=', 'secteurs.id')
              ->join('vendeurs as vendeur1', 'vente_secteurs.vendeur_id', '=', 'vendeur1.id')
              ->leftjoin('vendeurs as vendeur2', 'vente_secteurs.aideVendeur_id', '=', 'vendeur2.id')
              ->leftjoin('vendeurs as vendeur3', 'vente_secteurs.aideVendeur2_id', '=', 'vendeur3.id')
              ->leftjoin('bon_sorties', 'bon_sorties.id', '=', 'vente_secteurs.bonSortie_id')
              ->select(
                  'vente_secteurs.*',
                  // 'vente_secteurs.id as vente_secteurs_ID',
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

            return response()->json($venteSecteur);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function store(Request $request)
    {

        DB::beginTransaction();

        //  try {
            $validator = Validator::make($request->all(), [
                'dateEntree' => 'required',
                'kilometrageFait' => 'required',
                'Total_HT' => 'required',
                'TVA' => 'required',
                'Total_TVA' => 'required',
                'Total_TTC' => 'required',
                'bonSortie_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = venteSecteur::where('reference', $request->reference)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Le Vente Secteur ne peut pas être dupliqué'
                ], 400);
            }

            $bonSortie = bonSortie::find($request->bonSortie_id);
            if(!$bonSortie) {
                return response()->json([
                        'message' => 'Le Bon de Sortie introuvable'
                ], 400);
            }

            $camion = Camion::find($bonSortie->camion_id);
            if(!$camion) {
                return response()->json([
                        'message' => 'Le Camion introuvable'
                ], 400);
            }

             if($request->kilometrageFait <= $bonSortie->camionKM) {
                return response()->json([
                    'message' => 'la valeur de Kilometrage nest pas correct'
                ], 400);
            }

            $KmDone = $request->kilometrageFait - $bonSortie->camionKM;

            $Added = venteSecteur::create([
                'reference' => $bonSortie->reference,
                'dateEntree' => $request->dateEntree,
                'kilometrageFait' => $KmDone,
                'Total_HT' => $request->Total_HT,
                'TVA' => $request->TVA,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
                'bonSortie_id' => $bonSortie->id,
                'Confirme' => 0,
                'Total_Rester' => $request->Total_TTC,
                'vendeur_id' => $bonSortie->vendeur_id,
                'aideVendeur_id' => $bonSortie->aideVendeur_id,
                'aideVendeur2_id' => $bonSortie->aideVendeur2_id,
                'camion_id' => $bonSortie->camion_id,
                'secteur_id' => $bonSortie->secteur_id,
                'warehouse_id' => $bonSortie->warehouse_id,
            ]);

            if (!$Added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            foreach($request->Articles as $article) {
                venteSecteurArticle::create([
                   'venteSecteur_id' => $Added->id,
                   'article_id' => $article['article_id'],
                   'qte_sortie' => $article['qte_sortie'],
                   'qte_retourV' => $article['qte_retourV'],
                   'qte_perime' => $article['qte_perime'],
                   'qte_echange' => $article['qte_echange'],
                   'qte_gratuit' => $article['qte_gratuit'],
                   'qte_credit' => $article['qte_credit'],
                   'qte_vendu' => $article['qte_vendu'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_Vendu' => $article['Total_Vendu'],
           ]);

            }

            DB::commit();

            return response()->json([
                    'message' => 'Création réussie de Bon de vente secteur',
                    'id' => $Added->id
                ]);

        /*  } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        } */
    }

    public function markAsConfirmed($id)
    {

        DB::beginTransaction();
        try {
            $venteSecteur = venteSecteur::find($id);

            if(!$venteSecteur) {
                return response()->json([
                    'message' => 'Vente Secteur introuvable'
                ], 400);
            }

            $bonSortie = bonSortie::find($venteSecteur->bonSortie_id);
            if($bonSortie->Confirme != true) {
                return response()->json([
                    'message' => 'Le bon Sortie nest pas Confirmé'
                ], 400);
            }

            if($venteSecteur->Confirme == true) {
                return response()->json([
                    'message' => 'Vente Secteur est déjà Confirmé'
                ], 400);
            }

            $venteSecteur->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);


            $Articles = venteSecteurArticle::where('venteSecteur_id', $venteSecteur->id)->get();



            foreach($Articles as $article) {

                Inventory::updateOrCreate(
                    ['article_id' => $article['article_id'], 'warehouse_id' => $venteSecteur->warehouse_id],
                    ['actual_stock' => DB::raw('actual_stock + ' . $article['qte_retourV'])]
                );

            }

            DB::commit();
            return response()->json(['message' => 'confirmè avec succès'], 200);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 404);
        }

    }

    public function getbonSortie()
    {
        try {

            $linkedBonLivraisonVente = venteSecteur::pluck('bonSortie_id')->toArray();
            $bonLivraisonVente = bonSortie::where('Confirme', 1)
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

    public function show($id)
    {
        try {
            $facture = venteSecteur::find($id);

            if(!$facture) {
                return   response()->json(['message','Ventes Secteur reference'], 404);
            }

            $detailsfacture = venteSecteurArticle::where('venteSecteur_id', $facture->id)->get();

            $articles = [];

            foreach($detailsfacture as $detail) {
                $articl= Article::find($detail->article_id);

                $article = [
                    'article_id' => $detail->article_id,
                    'reference' => $articl->reference,
                    'article_libelle' => $articl->article_libelle,
                    'qte_sortie' => $detail->qte_sortie,
                    'qte_retourV' => $detail->qte_retourV,
                    'qte_perime' => $detail->qte_perime,
                    'qte_echange' => $detail->qte_echange,
                    'qte_gratuit' => $detail->qte_gratuit,
                    'qte_credit' => $detail->qte_credit,
                    'qte_vendu' => $detail->qte_vendu,
                    'Prix_unitaire' => $detail->Prix_unitaire,
                    'Total_Vendu' => $detail->Total_Vendu,
                ];
                $articles[] = $article;
            }

            $factures = venteSecteur::join('warehouses', 'vente_secteurs.warehouse_id', '=', 'warehouses.id')
            ->join('camions', 'vente_secteurs.camion_id', '=', 'camions.id')
            ->join('secteurs', 'vente_secteurs.secteur_id', '=', 'secteurs.id')
            ->join('vendeurs as vendeur1', 'vente_secteurs.vendeur_id', '=', 'vendeur1.id')
             ->leftjoin('vendeurs as vendeur2', 'vente_secteurs.aideVendeur_id', '=', 'vendeur2.id')
            ->leftjoin('vendeurs as vendeur3', 'vente_secteurs.aideVendeur2_id', '=', 'vendeur3.id')
            ->leftjoin('bon_sorties', 'bon_sorties.id', '=', 'vente_secteurs.bonSortie_id')
            ->select(
                'vente_secteurs.*',
                'warehouses.nom_Warehouse',
                'secteurs.secteur',
                'camions.matricule',
                'camions.marque',
                'camions.modele',
                'vendeur1.nomComplet as nomComplet1',
                'vendeur2.nomComplet as nomComplet2',
                'vendeur3.nomComplet as nomComplet3'
            )
            ->where('vente_secteurs.id', $id)
            ->first();

            $factureArray = $factures->toArray();
            $factureArray['Articles'] = $articles;

            return response()->json(['data' => $factureArray], 200);

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
            $venteSecteur = venteSecteur::find($id);

            if (!$venteSecteur) {
                return response()->json([
                    'message' => 'Bon Secteur de vente introuvable'
                ], 404);
            }

            if($venteSecteur->Confirme == true) {
                return response()->json([
                    'message' => 'Bon Secteur Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            venteSecteurArticle::where('blVente_id', $venteSecteur->id)->delete();
            $venteSecteur->delete();

            DB::commit();
            return response()->json([
                'message' => 'Le Bon Secteur de Vente nest plus disponible',
                'id' => $venteSecteur->id

            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function printvs($id, $isDownloaded)
    {
        try {

            $commande = venteSecteur::join('warehouses', 'vente_secteurs.warehouse_id', '=', 'warehouses.id')
            ->select('vente_secteurs.*', 'warehouses.nom_Warehouse')->withTrashed()
            ->find($id);

            if (!$commande) {
                return response()->json([
                    'message' => 'Ce Vente Secteur est introuvable'
                ], 409);
            }

            $articles = venteSecteurArticle::withTrashed()->select('vente_secteur_articles.*', 'articles.reference', 'articles.article_libelle', 'articles.unite')
                ->join('articles', 'vente_secteur_articles.article_id', '=', 'articles.id')
                ->where('venteSecteur_id', $commande->id)
                ->get();

            $vendeur1 = Vendeur::withTrashed()->where('id', $commande->vendeur_id)->first();
            $vendeur2 = Vendeur::withTrashed()->where('id', $commande->aideVendeur_id)->first();
            $vendeur3 = Vendeur::withTrashed()->where('id', $commande->aideVendeur2_id)->first();

            $company = Company::get()->first();

            $secteur = Secteur::withTrashed()->where('id', $commande->secteur_id) ->first();
            $dateTirage = Carbon::now();

            $camion = camion::withTrashed()->where('id', $commande->camion_id)->first();
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

            $pdf->loadView('Prints.vente.VenteSecteur', compact('commande', 'articles', 'company', 'pdf', 'vendeur1', 'vendeur2', 'vendeur3', 'secteur', 'dateTirage', 'camion'));

            if($isDownloaded === 'true') {
                return $pdf->download('Vente_Secteur_Nº'.$commande->reference.'.pdf');
            }

            return $pdf->stream('Vente_Secteur_Nº'.$commande->reference.'.pdf');

        } catch (Exception $e) {

            abort(404);
        }

    }

}
