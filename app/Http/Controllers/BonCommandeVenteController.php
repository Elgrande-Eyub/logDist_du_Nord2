<?php

namespace App\Http\Controllers;

use App\Http\Resources\bonCommandeVenteResource;
use App\Models\Article;
use App\Models\bonCommande_article;
use App\Models\bonCommandeVente;
use App\Models\bonCommandeVenteArticle;
use App\Models\client;
use App\Models\Company;
use App\Models\factureVente;
use App\Models\Fournisseur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BonCommandeVenteController extends Controller
{
    public function index()
    {
         try {

            $bonCommande = bonCommandeVente::orderByDesc('Numero_bonCommandeVente')
            ->leftjoin('clients','bon_commande_ventes.client_id','=','clients.id')
            ->select('bon_commande_ventes.*','clients.nom_Client')
            ->get();

            // return bonCommandeVenteResource::collection($bonCommande);
            return response()->json(['data'=>$bonCommande]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function store(Request $request)
    {

        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'client_id' => 'required',
                'Numero_bonCommandeVente' => 'required',
                'date_BCommandeVente' => 'required',
                'attachement' => 'nullable|mimes:jpeg,pngnjpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = bonCommandeVente::where('Numero_bonCommandeVente', $request->Numero_bonCommandeVente)->exists();
            if ($found) {
                DB::rollBack();
                return response()->json([
                    'message' => 'ce bon de commande est déjà en place.'
                ], 409);
            }

            $date = Carbon::parse($request->date_BCommandeVente);
            $Etat = $request->Confirme ? 'Recu' : 'Saisi';



            $added = bonCommandeVente::create([
                'client_id' => $request->client_id,
                'Numero_bonCommandeVente' => $request->Numero_bonCommandeVente,
                'Exercice' => $date->format('Y'),
                'Mois' => $date->format('n'),
                'Etat' => $Etat,
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_BCommandeVente' => $request->date_BCommandeVente,
                'Total_HT' => $request->Total_HT,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,

            ]);

            if (!$added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
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

                Storage::disk('bonCommandeVente')->put($imageName, file_get_contents($image));
                $added->update([
                    'attachement' => $imageName
                ]);
            }

            foreach ($request->Articles as $article) {
                bonCommandeVenteArticle::create([
                    'article_id' => $article['article_id'],
                    'Prix_unitaire' => $article['Prix_unitaire'],
                    'Quantity' => $article['Quantity'],
                    'Total_HT' => $article['Total_HT'],
                    'bcVente_id' => $added->id,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Création réussie de bons de commande.',
                        'id' =>    $added->id

                    ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }
    }


    public function CheckClientCredit($id)
    {
        try {
            $client = client::find($id);
            if (!$client) {
                return response()->json([
                    'message' => 'Le client est introuvable'
                ], 404);
            }

            $TotalCredit = FactureVente::where('client_id', $client->id)->sum('Total_Rester');

            if($TotalCredit > 0) {
                return response()->json([
                    'message' => 'Le Client '.$client->nom_Client.' a un crédit de '.$TotalCredit.'Dhs'
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

        try {
            $bonCommande = bonCommandeVente::find($id);

            if(!$bonCommande) {
                return response()->json([
                    'message' => 'Le bon de commande de vente introuvable'
                ], 400);
            }

            $bonCommande->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            return response()->json(['message' => 'Le bon commande de vente se confirme avec succès.']);

            DB::commit();
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }

    }

    public function show($id)
    {
        try {

            $bonCommande = bonCommandeVente::find($id);
            if(!$bonCommande) {
                return response()->json([
                    'message' => 'Le bon de commande de vente introuvable'
                ], 404);
            }

            $detailsCommande = bonCommandeVenteArticle::where('bcVente_id', $bonCommande->id)->get();

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
            $bonCommande = bonCommandeVente::withTrashed()->leftjoin('clients', 'bon_commande_ventes.client_id', '=', 'clients.id')->withTrashed()
            ->leftJoin('bon_livraison_ventes', 'bon_commande_ventes.id', '=', 'bon_livraison_ventes.bonCommandeVente_id')
            ->select('bon_commande_ventes.*', 'clients.nom_Client', 'bon_livraison_ventes.id as bonLivraisonVente_id')
            ->where('bon_commande_ventes.id', $id)
            ->first();


            $bonCommandeArray = $bonCommande->toArray();
            $bonCommandeArray['Articles'] = $articles;

            return response()->json($bonCommandeArray);


        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }


    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $bonCommandeVente = bonCommandeVente::findOrFail($id);

            if($bonCommandeVente) {

                if($bonCommandeVente->Confirme == true) {
                    return response()->json(['message' => 'vous ne pouvez pas modifier un commande confirmée']);
                }

            }

            $bonCommandeVente->client_id = $request->client_id;
            $bonCommandeVente->Numero_bonCommandeVente = $request->Numero_bonCommandeVente;
            $bonCommandeVente->date_BCommandeVente = $request->date_BCommandeVente;
            $bonCommandeVente->Confirme = $request->Confirme;
            $bonCommandeVente->Commentaire = $request->Commentaire;
            $bonCommandeVente->Total_HT = $request->Total_HT;
            $bonCommandeVente->remise = $request->remise;
            $bonCommandeVente->TVA = $request->TVA;
            $bonCommandeVente->Total_TVA = $request->Total_TVA;
            $bonCommandeVente->Total_TTC = $request->Total_TTC;

            $bonCommandeVente->save();

            // Delete existing bonCommandeVenteArticles
            bonCommandeVenteArticle::where('bonCommandeVente_id', $id)->delete();

            // Add updated bonCommandeVenteArticles
            foreach ($request->Articles as $article) {
                bonCommandeVenteArticle::create([
                    'article_id' => $article['article_id'],
                    'Prix_unitaire' => $article['Prix_unitaire'],
                    'Quantity' => $article['Quantity'],
                    'Total_HT' => $article['Total_HT'],
                    'bonCommandeVente_id' => $id,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Bon Commande de Vente updated successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }
    }



    public function destroy($id)
    {
        try {

            $bonCommandeVente = bonCommandeVente::find($id);

            if (!$bonCommandeVente) {
                return response()->json([
                    'message' => 'Le bon de commande de vente introuvable'
                ], 404);
            }

            if($bonCommandeVente->Confirme == true) {
                return response()->json([
                    'message' => 'bon Commande est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonCommandeVenteArticle::where('bonCommandeVente_id', $bonCommandeVente->id)->delete();

            $bonCommandeVente->delete();
            DB::commit();
            return response()->json([
                'message' => 'Le bon commande de vente a ete supprimer avec succès.',
                'id' => $bonCommandeVente->id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function printbcv($id, $condition, $isDownloaded)
    {
        // try {

        $commande = bonCommandeVente::find($id);
        $articles = bonCommandeVenteArticle::select('bon_commande_vente_articles.*', 'articles.*')
            ->join('articles', 'bon_commande_vente_articles.article_id', '=', 'articles.id')
            ->where('bcVente_id', $id)
            ->get();

        $client = client::withTrashed()->find($commande->client_id);

        $company = Company::get()->first();
        /*  $bank = BankAccount::get()->first();
 */
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

        // if ($condition === 'ac') {
        $pdf->loadView('Prints.vente.bonCommandeVente', compact('commande', 'articles', 'client', 'pdf', 'condition', 'company'));
        /*       } elseif ($condition === 'sc') {
                  $pdf->loadView('Prints.vente.bonCommandeVenteSC', compact('commande', 'articles', 'client',  'pdf'));
              }
 */

        if($isDownloaded === 'true') {
            return $pdf->download('Bon_Commande_Nº'.$commande->Numero_bonCommande.'.pdf');
        }

        return $pdf->stream('Bon_Commande_'.$commande->Numero_bonCommande.'.pdf');


        /*  } catch (Exception $e) {

             abort(404);
         }
 */
    }

}
