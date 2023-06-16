<?php

namespace App\Http\Controllers;

use App\Events\AlertStockProcessed;
use App\Models\Article;
use App\Models\BankAccount;
use App\Models\bonLivraison;
use App\Models\bonretourAchat;
use App\Models\bonretourAchatArticle;
use App\Models\Company;
use App\Models\Fournisseur;
use App\Models\Inventory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonretourAchatController extends Controller
{
    public function index()
    {
        try {
            $bonretourAchat  = bonretourAchat::join('fournisseurs', 'bonretour_achats.fournisseur_id', '=', 'fournisseurs.id')
            ->join('bon_livraisons', 'bonretour_achats.bonLivraison_id', '=', 'bon_livraisons.id')
            ->join('warehouses', 'bonretour_achats.warehouse_id', '=', 'warehouses.id')
            ->select('bonretour_achats.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_livraisons.Numero_bonLivraison')
            ->get();

            return response()->json($bonretourAchat);
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
            // Check if the Bon Reception is empty

            $validator = Validator::make($request->all(), [
                'Numero_bonRetour' => 'required',
                'date_BRetour' => 'required',
                'Total_HT' => 'required',
                'Total_TTC' => 'required',
                'bonLivraison_id' => 'required',
                'raison'=> 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $bonLivraison = bonLivraison::find($request->bonLivraison_id);

            if (!$bonLivraison) {
                return response()->json([
                    'message' => 'Le Bon de Livraison introuvable'
                ], 400);
            }
            if ($bonLivraison->Confirme != true) {
                return response()->json([
                    'message' => 'Le Bon de Livraison nest pas Confirmé'
                ], 400);
            }


            $bonRetour = bonretourAchat::where('Numero_bonRetour', $request->Numero_bonRetour)->exists();

            if($bonRetour) {
                return response()->json([
                    'message' => 'Le Bon Retour ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_BRetour);

            if($request->Confirme == true) {
                $Etat = 'Recu';
            } else {
                $Etat = 'saisi';
            }

            $Added = bonretourAchat::create([
                'Numero_bonRetour' => $request->Numero_bonRetour,
                'bonLivraison_id' => $request->bonLivraison_id,
                'fournisseur_id' => $bonLivraison->fournisseur_id,
                'warehouse_id' => $bonLivraison->warehouse_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('m'),
                'Etat' => $Etat,
                'Confirme' => 0,
                'Commentaire' => $request->Commentaire,
                'raison'=> $request->raison,
                'date_BRetour' => $request->date_BRetour,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
            ]);

            // Check if the Bon Commande was successfully created
            if (!$Added) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            // Add the bon Articles Related to bon Commande
            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                bonretourAchatArticle::create([
                   'bonretourAchat_id' => $Added->id,
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
              ]);

            }

            DB::commit();

            return response()->json([
                    'message' => 'Création réussie de Bon Retour',
                    'id' => $Added->id
                ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function getBonLivraison()
    {
        try {
            $linkedBonLivraison = bonretourAchat::pluck('bonLivraison_id')->toArray();
            $bonLivraison = bonLivraison::where('Confirme', 1)
                                ->whereNotIn('id', $linkedBonLivraison)
                                ->get();

            return response()->json($bonLivraison);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function markAsConfirmed($id, Request $request)
    {

        DB::beginTransaction();
        // try {
            $bonretourAchat = bonretourAchat::find($id);

            if(!$bonretourAchat) {
                return response()->json([
                    'message' => 'Bon de Retour introuvable'
                ], 400);
            }

            if($bonretourAchat->Confirme == true) {
                return response()->json([
                    'message' => 'Bon de Retour est déjà Confirmé'
                ], 400);
            }

            $Articles =   bonretourAchatArticle::where('bonretourAchat_id', $bonretourAchat->id)->get();
            $isAlerted = false;
            foreach($Articles as $article) {

                $CheckStock = Inventory::updateOrCreate(
                    ['article_id' => $article['article_id'], 'warehouse_id' => $bonretourAchat->warehouse_id],
                    ['actual_stock' => DB::raw('actual_stock - ' . $article['Quantity'])]
                );

                if($article->alert_stock >= $CheckStock->actual_stock) {
                    $isAlerted = true;
                }
            }

            $bonretourAchat->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            if($isAlerted) {
                event(new AlertStockProcessed());
            }

            DB::commit();
            return response()->json(['message' => 'confirmè avec succès'], 200);

      /*   } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 404);
        } */

    }

    public function getNumerobr()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = bonretourAchat::withTrashed()->latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->Numero_bonRetour, -4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->Numero_bonRetour, 2, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'R-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['num_bonRetour' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function show($id)
    {
        try {

            $bonretourAchat = bonretourAchat::find($id);
            if(!$bonretourAchat) {
                return response()->json([
                    'message' => 'Bon de Retour introuvable'
                ], 404);
            }

            $detailsCommande = bonretourAchatArticle::where('bonretourAchat_id', $bonretourAchat->id)->get();

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

            $bonretourAchat  = bonretourAchat::leftjoin('fournisseurs', 'bonretour_achats.fournisseur_id', '=', 'fournisseurs.id')->withTrashed()
            ->join('bon_livraisons', 'bonretour_achats.bonLivraison_id', '=', 'bon_livraisons.id')
            ->join('warehouses', 'bonretour_achats.warehouse_id', '=', 'warehouses.id')
            ->select('bonretour_achats.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_livraisons.Numero_bonLivraison')
            ->where('bonretour_achats.id', $id)
            ->first();

            $bonretourAchatArray = $bonretourAchat->toArray();
            $bonretourAchatArray['Articles'] = $articles;

            return response()->json($bonretourAchatArray);

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

            $bonretourAchat = bonretourAchat::find($id);

            if (!$bonretourAchat) {
                return response()->json([
                    'message' => 'Bon Retour introuvable'
                ], 404);
            }

            if($bonretourAchat->Confirme == true) {

                return response()->json([
                    'message' => 'Bon Retour est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonretourAchatArticle::where('bonretourAchat_id', $bonretourAchat->id)->forceDelete();
            $bonretourAchat->forceDelete();

            DB::commit();
            return response()->json([
                'message' => 'Bon Retour nest plus disponible',
                'id' => $bonretourAchat->id

            ]);
        } catch (Exception $e) {

            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function printbonRetour($id, $isDownloaded)
    {
        try {

            $commande  = bonretourAchat::join('fournisseurs', 'bonretour_achats.fournisseur_id', '=', 'fournisseurs.id')
            ->join('bon_livraisons', 'bonretour_achats.bonLivraison_id', '=', 'bon_livraisons.id')
            ->join('warehouses', 'bonretour_achats.warehouse_id', '=', 'warehouses.id')
            ->select('bonretour_achats.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_livraisons.Numero_bonLivraison')
            ->where('bonretour_achats.id', $id)
            ->first();

            $articles = bonretourAchatArticle::select('bonretour_achat_articles.*', 'articles.*')
                ->join('articles', 'bonretour_achat_articles.article_id', '=', 'articles.id')
                ->where('bonretourAchat_id', $id)
                ->get();

            $bank = BankAccount::get()->first();
            $fournisseur = Fournisseur::find($commande->fournisseur_id);
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

            $pdf->loadView('Prints.bonretourAchat', compact('commande', 'articles', 'fournisseur', 'bank', 'company', 'pdf'));

            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Livriason_'.$commande->Numero_bonRetour.'.pdf');
            }

            return $pdf->stream('Bon_Livriason_'.$commande->Numero_bonRetour.'.pdf');

        } catch (Exception $e) {
            abort(404);

        }
    }
}
