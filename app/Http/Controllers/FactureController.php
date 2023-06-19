<?php

namespace App\Http\Controllers;

use App\Http\Resources\factureAchatResource;
use App\Http\Resources\FactureAchatResource as ResourcesFactureAchatResource;
use App\Models\Article;
use App\Models\avoirsAchat;
use App\Models\BankAccount;
use App\Models\bonLivraison;
use App\Models\bonretourAchat;
use App\Models\Company;
use App\Models\facture;
use App\Models\facture_article;
use App\Models\factureAvoirsachat;
use App\Models\Fournisseur;
use App\Models\Journal;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Normalizer\SlugNormalizer;



use Money\Currency;

class FactureController extends Controller
{
    public function index()
    {
        try {

            // $factures = facture::all();

            $factures = facture::join('bon_livraisons', 'factures.bonLivraison_id', '=', 'bon_livraisons.id')
            ->join('fournisseurs', 'factures.fournisseur_id', '=', 'fournisseurs.id')
            // ->leftJoin('avoirs_achats', 'avoirs_achats.factureAchat_id', '=', 'factures.id')
            ->select('factures.*', 'bon_livraisons.Numero_bonLivraison', 'fournisseurs.fournisseur')
            ->get();

            //return  factureAchatResource::collection($factures);
            return response()->json(['data'=>$factures]);

        } catch(Exception $e) {


            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);

        }

    }

    public function getBonLivraison()
    {
        try {

            $linkedBonLivraison = facture::where('isChange', 0)->whereNot('bonLivraison_id', null)->pluck('bonLivraison_id')->toArray();
            $bonLivraisons = bonLivraison::where('Confirme', 1)
                           ->whereNotIn('id', $linkedBonLivraison)
                           ->where('isChange', 0)
                           ->get();

            return response()->json($bonLivraisons);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getAvoirsUnlinked($id){
        // try{

            $bonLivraison = bonLivraison::find($id);

            if(!$bonLivraison){
                return response()->json([
                    'message' => 'Bon Livriason introuvable'
                ], 400);
            }

            $avoirsAchat = avoirsAchat::where('Confirme', 1)
            ->where('fournissuer_id', $bonLivraison->fournisseur_id)
            ->where('isLinked',0)
            ->get();

            return response()->json($avoirsAchat);

       /*  } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        } */

    }

    public function getBonLivraisonChange()
    {
        try {

            $linkedBonLivraison = facture::where('Confirme', 1)->where('isChange', 1)->whereNotNull('bonLivraison_id')->pluck('bonLivraison_id')->toArray();
            $bonLivraisons = bonLivraison::where('Confirme', 1)
                                        ->whereNotIn('id', $linkedBonLivraison)
                                        ->where('isChange', 1)
                                        ->get();

            return response()->json($bonLivraisons);



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

        try {

            $validator = Validator::make($request->all(), [
                'numero_Facture' => 'required',
                // 'fournisseur_id' => 'required',
                'Confirme' => 'required',
                'date_Facture' => 'required',
                'Total_HT' => 'required',
                'Total_TVA' => 'required',
                'Total_TTC' => 'required',
                // 'attachement' => 'nullable|mimes:jpeg,png,jpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = facture::where('numero_Facture', $request->numero_Facture)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'La Facture ne peut pas être dupliqué'
                ], 400);
            }

            $foundBL = bonLivraison::where('id', $request->bonLivraison_id)->exists();

            if (!$foundBL) {
                return response()->json([
                    'message' => 'Bon Livriason introuvable'
                ], 400);
            }

            $bonLivraison = bonLivraison::find($request->bonLivraison_id);

            $date = Carbon::parse($request->date_Facture);

            $Added = facture::create([
                'numero_Facture' => $request->numero_Facture,
                'bonLivraison_id' => $request->bonLivraison_id,
                'fournisseur_id' => $bonLivraison->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('n'),
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_Facture' => $request->date_Facture,
                'conditionPaiement'=> $request->conditionPaiement,
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

            if ($request->hasFile('attachement')) {
                $image = $request->file('attachement');
                $imageName =  Carbon::now()->timestamp.'.'.$image->getClientOriginalExtension();

                $extension = $image->getClientOriginalExtension();
                $validExtensions = ['pdf', 'jpg', 'jpeg', 'png','PDF', 'JPG', 'JPEG', 'PNG'];

                if (!in_array($extension, $validExtensions)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Veuillez télécharger une pièce jointe valide IMG/PDF'
                    ], 404);
                }

                Storage::disk('FactureAchat')->put($imageName, file_get_contents($image));
                $Added->update([
                    'attachement' => $imageName
                ]);
            }

            if($request->isChange && $request->hasAvoirs) {
                DB::rollBack();
                return response()->json([
                   'message' => 'un seul peut être vrai (Change ou Avoirs Paye)'
                ], 404);
            }

            if($request->isChange) {

                if($bonLivraison->isChange == false) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'ce bon Livraison nest pas un bon de changement'
                    ], 404);
                }

                $BonRetour = bonretourAchat::where('id', $bonLivraison->bonretourAchat_id)->first();

                $etat = "En Cours";
                if($Added->Total_Rester == $BonRetour->Total_TTC) {
                    $etat ='Paye';
                }

                $Added->update([
                    'Total_Rester' => $request->Total_TTC - $BonRetour->Total_TTC,
                    'Total_Regler' => $BonRetour->Total_TTC,
                    'EtatPaiement' => $etat,
                    'isChange' => $request->isChange
                ]);


            }

            if($request->hasAvoirs) {

                if(empty($request->Avoirs)) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'doit etre selecter un/des avoirs pour continue cet operation'
                    ], 404);
                }

                $TotalAvoirs = 0;
                foreach($request->Avoirs as $avoirs) {

                    $isExists = avoirsAchat::find($avoirs);
                    if(!$isExists) {
                        DB::rollBack();
                        return response()->json([
                           'message' => 'Avoirs introuvable'
                        ], 404);
                    }
                    $avoirsAchat = avoirsAchat::where('id', $avoirs)->first();

                    factureAvoirsachat::created([
                        'avoirsAchat_id' => $Added->id,
                        'factureAchat_id'=> $avoirs
                    ]);

                    $TotalAvoirs += $avoirsAchat->Total_TTC;

                    $avoirsAchat->update([
                        'factureChange_id'=> $Added->id,
                        'isLinked' => 1
                    ]);
                }

                $etat = "En Cours";
                if($TotalAvoirs == $Added->Total_TTC) {
                    $etat ='Paye';
                }

                $Added->update([
                    'Total_Rester' => $request->Total_TTC - $TotalAvoirs,
                    'Total_Regler' => $TotalAvoirs,
                    'EtatPaiement' => $etat,
                    'hasAvoirs' => $request->hasAvoirs
                ]);

            }

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                facture_article::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
                   'facture_id' => $Added->id

            ]);
            }

            DB::commit();
            return response()->json([
                    'message' => 'Facture créée avec succès',
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

            $facture = facture::find($id);

            if(!$facture) {
                return   response()->json(['message','Facture introuvable'], 404);
            }

            $detailsfacture = facture_article::where('facture_id', $facture->id)->get();

            $articles = [];

            foreach($detailsfacture as $detail) {
                $articl= Article::withTrashed()->find($detail->article_id);
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

            $factures = facture::leftjoin('bon_livraisons', 'factures.bonLivraison_id', '=', 'bon_livraisons.id')
            ->leftjoin('fournisseurs', 'factures.fournisseur_id', '=', 'fournisseurs.id')
            ->leftjoin('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
            ->select('factures.*', 'bon_livraisons.Numero_bonLivraison', 'fournisseurs.fournisseur as fournisseur', 'bon_commandes.Numero_bonCommande')
            ->where('factures.id', $id)
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

            $factureFounded = facture::find($id);

            if (!$factureFounded) {
                return response()->json([
                    'message' => 'facture introuvable'
                ], 404);
            }

            if($factureFounded->Confirme == true) {
                return response()->json([
                    'message' => 'La facture est Confirmé, ne peut pas être supprimé'
                ], 400);
            }


            $factureFounded->delete();

            facture_article::where('facture_id', $factureFounded->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'La Facture n`est plus disponible',
                'id' => $factureFounded->id
            ]);
        } catch (Exception $e) {
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
            $facture = facture::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'La facture introuvable'
                ], 400);
            }

            $facture->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'La facture se confirmè avec succès',
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
            $facture = facture::find($id);

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

    public function facturePrint($id, $isDownloaded)
    {
        try {
            $commande = facture::join('bon_livraisons', 'factures.bonLivraison_id', '=', 'bon_livraisons.id')
            ->join('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
            ->join('warehouses', 'bon_livraisons.warehouse_id', '=', 'warehouses.id')
            ->select('factures.*', 'bon_livraisons.Numero_bonLivraison', 'bon_livraisons.id', 'bon_commandes.Numero_bonCommande as Numero_bonCommande', 'warehouses.*')
            ->where('factures.id', $id)
            ->first();

            $bank = BankAccount::get()->first();

            $articles = facture_article::select('facture_articles.*', 'articles.*')
                ->join('articles', 'facture_articles.article_id', '=', 'articles.id')
                ->where('facture_id', $id)
                ->get();

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

            $pdf->loadView('Prints.FactureAchat', compact('commande', 'articles', 'fournisseur', 'bank', 'company', 'pdf'));

            if($isDownloaded === 'true') {
                return $pdf->download('Facture_Nº'.$commande->numero_Facture.'.pdf');
            }

            return $pdf->stream('Facture_'.$commande->numero_Facture.'.pdf');

        } catch (Exception $e) {
            abort(404);

        }
    }
}
