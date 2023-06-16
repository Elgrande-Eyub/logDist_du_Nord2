<?php

namespace App\Http\Controllers;

use App\Http\Resources\bonCommandeResource;
use App\Http\Resources\bonLivraisonResource;
use App\Models\Article;
use App\Models\avoirsAchat;
use App\Models\BankAccount;
use App\Models\bonCommande;
use App\Models\bonCommande_article;
use App\Models\bonLivraison;
use App\Models\bonLivraison_article;
use App\Models\bonLivraisonArticle;
use App\Models\bonretourAchat;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\Fournisseur;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BonLivraisonController extends Controller
{
    public function index()
    {
        try {

            $bonLivraison = bonLivraison::leftjoin('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
            ->leftjoin('fournisseurs', 'bon_livraisons.fournisseur_id', '=', 'fournisseurs.id')
            ->leftjoin('bonretour_achats', 'bon_livraisons.bonretourAchat_id', 'bonretour_achats.id')
            ->select('bon_livraisons.*', 'bon_commandes.Numero_bonCommande', 'fournisseurs.fournisseur',  'bonretour_achats.Numero_bonRetour')
            ->get();


            return response()->json(['data'=>$bonLivraison]);

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
                'fournisseur_id' => 'required',
                'Numero_bonLivraison' => 'required',
                'warehouse_id' => 'required',
                'date_Blivraison' => 'required',
                'Total_HT' => 'required',
                'Total_TTC' => 'required',
                // 'attachement' => 'nullable|mimes:jpeg,png,jpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = bonLivraison::where('Numero_bonLivraison', $request->Numero_bonLivraison)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Le Bon de Livraison ne peut pas être dupliqué'
                ], 400);
            }

            if($request->bonCommande_id) {
                $bonCommande = bonCommande::where('id', $request->bonCommande_id)->exists();

                if(!$bonCommande) {
                    return response()->json([
                        'message' => 'Le Bon de Commande introuvable'
                    ], 400);
                }
            }

            $date = Carbon::parse($request->date_Blivraison);

            if($request->Confirme == true) {
                $Etat = 'Recu';
            } else {
                $Etat = 'saisi';
            }

            $Added = bonLivraison::create([
                'Numero_bonLivraison' => $request->Numero_bonLivraison,
                'bonCommande_id' => $request->bonCommande_id,
                'fournisseur_id' => $request->fournisseur_id,
                'warehouse_id' => $request->warehouse_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('m'),
                'Etat' => $Etat,
                'Confirme' => 0,
                'Commentaire' => $request->Commentaire,
                'date_Blivraison' => $request->date_Blivraison,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
            ]);

            if (!$Added) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
                ], 400);
            }

            if ($request->hasFile('attachement')) {
                $image = $request->file('attachement');
                $imageName =  Carbon::now()->timestamp.'.'.$image->getClientOriginalExtension();

                $extension = $image->getClientOriginalExtension();
                $validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

                if (!in_array($extension, $validExtensions)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Veuillez télécharger une pièce jointe valide IMG/PDF'
                    ], 404);
                }

                Storage::disk('bonLivraisonAchat')->put($imageName, file_get_contents($image));
                $Added->update([
                    'attachement' => $imageName
                ]);
            }

            // Check if the bon Livraison is a simple BonLivraison or Its a Change of goods
            if($request->isChange == true) {
                $isBonRetourExists = bonretourAchat::find($request->bonretourAchat_id);

                if(!$isBonRetourExists) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Bon Retour introuvable'
                    ], 404);
                }

                if($isBonRetourExists->Confirme != true) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le Bon Retour doit être confirmé'
                    ], 404);
                }

                if($isBonRetourExists->bonLivraisonChange_id != null) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le Bon Retour Est deja lie avec another Bon Livraison'
                    ], 404);
                }

                $Added->update([
                    'isChange' => true,
                    'bonretourAchat_id' => $request->bonretourAchat_id
                ]);

                $isBonRetourExists->update([
                    'bonLivraisonChange_id' => $Added->id
                ]);
            }

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                bonLivraisonArticle::create([
                   'bonLivraison_id' => $Added->id,
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
               ]);

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

    public function getBonCommande()
    {
        try {

            $linkedBonCommandes = bonLivraison::whereNotNull('bonCommande_id')->pluck('bonCommande_id')->toArray();
$bonCommandes = bonCommande::where('Confirme', 1)
                            ->whereNotIn('id', $linkedBonCommandes)
                            ->get();

            return response()->json($bonCommandes);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
            'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getBonRetour()
    {
        try {
            $BonAchats = bonretourAchat::where('bonLivraisonChange_id',null)->get();
            return response()->json($BonAchats);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }


   /*  public function addAttachement($id, Request $request)
    {
        // try {
            DB::beginTransaction();

            $bonLivraison = bonLivraison::find($id);
            if(!$bonLivraison) {
                return response()->json([
                    'message' => 'Bon de Livraison introuvable'
                ], 404);
            }

            if($bonLivraison->attachement !=null) {
                return response()->json([
                    'message' => 'ce bon est déjà à une pièce jointe'
                ], 404);
            }

            if(!$request->hasFile('attachement')) {
                return response()->json([
                    'message' => 'Veuillez télécharger une pièce jointe valide IMG/PDF'
                ], 404);
            }

            $image = $request->file('attachement');
            $imageName =  Carbon::now()->timestamp.'.'.$image->getClientOriginalExtension();
            Storage::disk('bonLivraisonAchat')->put($imageName, file_get_contents($image));

            $bonLivraison->update([
                'attachement' => $imageName
            ]);

            DB::commit();

            return response()->json([
                                'message' => 'Pièce jointe  ajoute avec succès.',

                            ]);

        /* } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
            'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    } */

    public function show(bonLivraison $bonLivraison, $id)
    {
        try {

            $bonLivraison = bonLivraison::find($id);
            if(!$bonLivraison) {
                return response()->json([
                    'message' => 'Bon de Livraison introuvable'
                ], 404);
            }

            $detailsCommande = bonLivraisonArticle::where('bonLivraison_id', $bonLivraison->id)->get();

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

            $bonLivraison = bonLivraison::withTrashed()->join('fournisseurs', 'bon_livraisons.fournisseur_id', '=', 'fournisseurs.id')
                ->leftjoin('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
                ->join('warehouses', 'bon_livraisons.warehouse_id', '=', 'warehouses.id')
                ->leftJoin('factures', 'bon_livraisons.id', '=', 'factures.bonLivraison_id')
                ->leftjoin('bonretour_achats', 'bon_livraisons.bonretourAchat_id', 'bonretour_achats.id')
                ->select('bon_livraisons.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_commandes.Numero_bonCommande', 'factures.id as facture_id',  'bonretour_achats.Numero_bonRetour')
                ->where('bon_livraisons.id', $id)
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



    public function markAsConfirmed($id)
    {

        DB::beginTransaction();
        try {
            $bonLivraison = bonLivraison::find($id);

            if(!$bonLivraison) {
                return response()->json([
                    'message' => 'Bon de Livraison introuvable'
                ], 400);
            }

            if($bonLivraison->Confirme == true) {
                return response()->json([
                    'message' => 'Bon de Livraison est déjà Confirmé'
                ], 400);
            }

            $bonLivraison->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            $Articles =   bonLivraisonArticle::where('bonLivraison_id', $bonLivraison->id)->get();

            foreach($Articles as $article) {

                Inventory::updateOrCreate(
                    ['article_id' => $article['article_id'], 'warehouse_id' => $bonLivraison->warehouse_id],
                    ['actual_stock' => DB::raw('actual_stock + ' . $article['Quantity'])]
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


    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $BLivraison_Founded = bonLivraison::find($id);


            if (!$BLivraison_Founded) {
                return response()->json([
                    'message' => 'Bon Livraison introuvable'
                ], 404);
            }

            if($BLivraison_Founded->Confirme == true) {
                return response()->json([
                    'message' => 'bon Livraison est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonLivraisonArticle::where('bonLivraison_id', $BLivraison_Founded->id)->delete();
            $BLivraison_Founded->delete();

            DB::commit();
            return response()->json([
                'message' => 'Bon Livraison nest plus disponible',
                'id' => $BLivraison_Founded->id

            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }



      public function printbonLivraison($id, $isDownloaded)
      {
          try {

              $commande =  $bonLivraison = bonLivraison::withTrashed()->join('fournisseurs', 'bon_livraisons.fournisseur_id', '=', 'fournisseurs.id')
              ->join('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
              ->join('warehouses', 'bon_livraisons.warehouse_id', '=', 'warehouses.id')
              ->select('bon_livraisons.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_commandes.Numero_bonCommande')
              ->where('bon_livraisons.id', $id)
              ->first();
              $articles = bonLivraisonArticle::withTrashed()->select('bon_livraison_articles.*', 'articles.*')
                  ->join('articles', 'bon_livraison_articles.article_id', '=', 'articles.id')
                  ->where('bonLivraison_id', $id)
                  ->get();

              $bank = BankAccount::get()->first();
              $fournisseur = Fournisseur::withTrashed()->find($commande->fournisseur_id);
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

              $pdf->loadView('Prints.bonLivraison', compact('commande', 'articles', 'fournisseur', 'bank', 'company', 'pdf'));

              if($isDownloaded === 'true') {
                  return $pdf->download('Bon_Livriason_'.$commande->Numero_bonLivraison.'.pdf');
              }

              return $pdf->stream('Bon_Livriason_'.$commande->Numero_bonLivraison.'.pdf');

          } catch (Exception $e) {
              abort(404);

          }
      }

      public function printbonReception($id, $isDownloaded)
      {
          try {

              $commande  = bonLivraison::withTrashed()->join('fournisseurs', 'bon_livraisons.fournisseur_id', '=', 'fournisseurs.id')
              ->join('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
              ->join('warehouses', 'bon_livraisons.warehouse_id', '=', 'warehouses.id')
              ->select('bon_livraisons.*', 'fournisseurs.fournisseur', 'warehouses.nom_Warehouse', 'bon_commandes.Numero_bonCommande')
              ->where('bon_livraisons.id', $id)
              ->first();

              $articles = bonLivraisonArticle::withTrashed()->select('bon_livraison_articles.*', 'articles.*')
                  ->join('articles', 'bon_livraison_articles.article_id', '=', 'articles.id')
                  ->where('bonLivraison_id', $id)
                  ->get();

              $bank = BankAccount::get()->first();
              $fournisseur = Fournisseur::withTrashed()->find($commande->fournisseur_id);
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

              $pdf->loadView('Prints.bonReception', compact('commande', 'articles', 'fournisseur', 'bank', 'company', 'pdf'));

              if($isDownloaded === 'true') {
                  return $pdf->download('Bon_Reception_de'.$commande->Numero_bonLivraison.'.pdf');
              }

              return $pdf->stream('Bon_Reception_de'.$commande->Numero_bonLivraison.'.pdf');

          } catch (Exception $e) {
              abort(404);

          }
      }
}
