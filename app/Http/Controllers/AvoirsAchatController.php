<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\avoirsAchat;
use App\Models\avoirsAchatArticle;
use App\Models\bonretourAchat;
use App\Models\bonretourAchatArticle;
use App\Models\facture;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AvoirsAchatController extends Controller
{
    public function index()
    {

        try {

            $Avoirs = avoirsAchat::join('factures', 'avoirs_achats.factureAchat_id', '=', 'factures.id')
            ->join('fournisseurs', 'avoirs_achats.fournisseur_id', '=', 'fournisseurs.id')
            ->leftJoin('bonretour_achats', 'bonretour_achats.bonLivraison_id', '=', 'factures.bonLivraison_id')
            ->select('avoirs_achats.*', 'factures.numero_Facture', 'factures.id as facture_id', 'fournisseurs.fournisseur', 'bonretour_achats.Numero_bonRetour', 'bonretour_achats.id as bonRetourAchat_id')
            ->get();

            return response()->json(['data'=>$Avoirs]);

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
                'numero_avoirsAchat' => 'required',
                'date_avoirs' => 'required',
                'factureAchat_id' =>'required',
                'Total_HT' => 'required',
                'Total_TVA' => 'required',
                'Total_TTC' => 'required',
                'attachement' => 'nullable|mimes:jpeg,png,jpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $facture = facture::find($request->factureAchat_id);
            if (!$facture) {
                return response()->json([
                    'message' => 'La facture introuvable'
                ], 400);
            }

            if ($facture->Confirme != true) {
                return response()->json([
                    'message' => 'La facture nest pas Confirmé'
                ], 400);
            }

            $avoirsAchat = avoirsAchat::where('numero_avoirsAchat', $request->numero_avoirsAchat)->exists();
            if ($avoirsAchat) {
                return response()->json([
                    'message' => 'L`Avoirs ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_avoirs);

            $Added = avoirsAchat::create([
                'numero_avoirsAchat' => $request->numero_avoirsAchat,
                'factureAchat_id' => $request->factureAchat_id,
                'raison' => $request->raison,
                'fournisseur_id' => $facture->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('n'),
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'conditionPaiement'=> $request->conditionPaiement,
                'date_avoirs' => $request->date_avoirs,
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
                Storage::disk('bonLivraisonAchat')->put($imageName, file_get_contents($image));
                $Added->update([
                    'attachement' => $imageName
                ]);
            }

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                avoirsAchatArticle::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
                   'avoirsAchat_id' => $Added->id
            ]);
            }

            DB::commit();
            return response()->json([
                    'message' => 'Avoirs créée avec succès',
                    'id' => $Added->id
                ]);

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
            $facture = avoirsAchat::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'L`Avoirs introuvable'
                ], 400);
            }

            $facture->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'L`Avoirs se confirmè avec succès',
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
            $facture = avoirsAchat::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'L`Avoirs introuvable'
                ], 400);
            }

            if($facture->Confirme == false) {
                return response()->json([
                    'message' => 'L`Avoirs doit être mis en œuvre Confirmé'
                ], 400);
            }

            $facture->update([
                'EtatPaiement' => 'Paye',
                'Total_Regler' => $facture->Total_TTC,
                'Total_Rester'=> 0
            ]);

            DB::commit();
            return response()->json([
                'message' => 'L`Avoirs est marquée comme payée',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getFactures()
    {
        try {
            $linkedFacture = avoirsAchat::pluck('factureAchat_id')->toArray();
            $Facture = facture::where('Confirme', 1)
                                ->whereNotIn('id', $linkedFacture)
                                ->get();

            return response()->json($Facture);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function getArticlesBonRetour($id)
    {
        try {

            $bonretour = facture::leftjoin('bon_livraisons', 'factures.bonLivraison_id', '=', 'bon_livraisons.id')
                        ->leftJoin('bonretour_achats', 'bon_livraisons.id', '=', 'bonretour_achats.bonLivraison_id')
                        ->select('bonretour_achats.*')
                        ->where('factures.id', $id)
                        ->first();

            if($bonretour->id == null){
                return response()->json([
                    'message' => 'Cet Facture navrois pas un bon Retour'
                ], 404);

            }

            $articles = bonretourAchatArticle::where('bonretourAchat_id', $bonretour->id)->get();
            $bonretourArray = $bonretour->toArray();
            $bonretourArray['Articles'] = $articles;
            return response()->json($bonretourArray);


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

            $Avoirs = avoirsAchat::find($id);

            if(!$Avoirs) {
                return   response()->json(['message','Avoirs introuvable'], 404);
            }

            $detailsfacture = avoirsAchatArticle::where('avoirsAchat_id', $Avoirs->id)->get();

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

            $Avoirs = avoirsAchat::withTrashed()->join('factures', 'avoirs_achats.factureAchat_id', '=', 'factures.id')
            ->join('fournisseurs', 'avoirs_achats.fournisseur_id', '=', 'fournisseurs.id')
            ->leftJoin('bonretour_achats', 'bonretour_achats.bonLivraison_id', '=', 'factures.bonLivraison_id')
            ->select('avoirs_achats.*', 'factures.numero_Facture', 'factures.id as facture_id', 'fournisseurs.fournisseur', 'bonretour_achats.Numero_bonRetour', 'bonretour_achats.id as bonRetourAchat_id')
            ->where('avoirs_achats.id', $id)
            ->first();

            $factureArray = $Avoirs->toArray();
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

            $avoirsAchat = avoirsAchat::find($id);

            if (!$avoirsAchat) {
                return response()->json([
                    'message' => 'Avoirs introuvable'
                ], 404);
            }

            if($avoirsAchat->Confirme == true) {
                return response()->json([
                    'message' => 'Avoirs est Confirmé, ne peut pas être supprimé'
                ], 400);
            }

            $avoirsAchat->delete();

            avoirsAchatArticle::where('avoirsAchat_id', $avoirsAchat->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'LAvoirs n`est plus disponible',
                'id' => $avoirsAchat->id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }
}
