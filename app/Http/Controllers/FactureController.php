<?php

namespace App\Http\Controllers;

use App\Http\Resources\factureAchatResource;
use App\Http\Resources\FactureAchatResource as ResourcesFactureAchatResource;
use App\Models\Article;
use App\Models\BankAccount;
use App\Models\bonLivraison;
use App\Models\Company;
use App\Models\facture;
use App\Models\facture_article;
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
            ->join('fournisseurs','factures.fournisseur_id','=','fournisseurs.id')
            ->leftJoin('avoirs_achats', 'avoirs_achats.factureAchat_id', '=', 'factures.id')
            ->select('factures.*', 'bon_livraisons.Numero_bonLivraison', 'avoirs_achats.id as avoir_id','avoirs_achats.id as numero_avoirsAchat','fournisseurs.fournisseur')
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
            $linkedBonLivraison = facture::pluck('bonLivraison_id')->toArray();
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

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'numero_Facture' => 'required',
                'fournisseur_id' => 'required',
                'Confirme' => 'required',
                'date_Facture' => 'required',
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

            $found = facture::where('numero_Facture', $request->numero_Facture)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'La Facture ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_Facture);

            $Added = facture::create([
                'numero_Facture' => $request->numero_Facture,
                'bonLivraison_id' => $request->bonLivraison_id,
                'fournisseur_id' => $request->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('n'),
                // 'EtatPayment' => $request->EtatPayment, // impaye , en cour , paye
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_Facture' => $request->date_Facture,
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
                Storage::disk('FactureAchat')->put($imageName, file_get_contents($image));
                $Added->update([
                    'attachement' => $imageName
                ]);
            }

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0 ){
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

            $factures = facture::join('bon_livraisons', 'factures.bonLivraison_id', '=', 'bon_livraisons.id')

            ->join('fournisseurs', 'factures.fournisseur_id', '=', 'fournisseurs.id')
            ->join('bon_commandes', 'bon_livraisons.bonCommande_id', '=', 'bon_commandes.id')
            ->select('factures.*', 'bon_livraisons.Numero_bonLivraison', 'fournisseurs.fournisseur as fournisseur', 'bon_commandes.Numero_bonCommande as Numero_bonCommande')
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
