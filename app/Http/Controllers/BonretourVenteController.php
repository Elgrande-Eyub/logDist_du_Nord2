<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\bonLivraisonVente;
use App\Models\bonretourVente;
use App\Models\bonretourVenteArticle;
use App\Models\Inventory;
use App\Models\warehouse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BonretourVenteController extends Controller
{
    public function index()
    {
        try {
            $bonRetour = bonretourVente::join('clients', 'bonretour_ventes.client_id', '=', 'clients.id')
            ->join('bon_livraison_ventes', 'bonretour_ventes.bonLivraison_id', '=', 'bon_livraison_ventes.id')
            ->join('warehouses', 'bonretour_ventes.warehouse_id', '=', 'warehouses.id')
            ->select('bonretour_ventes.*', 'clients.nom_Client', 'warehouses.nom_Warehouse', 'bon_livraison_ventes.Numero_bonLivraisonVente', 'bon_livraison_ventes.date_BlivraisonVente')
            ->get();

            return response()->json($bonRetour);
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

            $bonLivraison = bonLivraisonVente::find($request->bonLivraison_id);

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

            $bonRetour = bonretourVente::where('Numero_bonRetour', $request->Numero_bonRetour)->exists();

            if($bonRetour) {
                return response()->json([
                    'message' => 'Le Bon Retour ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_BRetour);

            $Added = bonretourVente::create([
                'Numero_bonRetour' => $request->Numero_bonRetour,
                'bonLivraison_id' => $request->bonLivraison_id,
                'client_id' => $bonLivraison->client_id,
                'warehouse_id' => $bonLivraison->warehouse_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('m'),
                'Etat' => 'Recu',
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

                Storage::disk('bonRetourVente')->put($imageName, file_get_contents($image));
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

                bonretourVenteArticle::create([
                   'bonretourVente_id' => $Added->id,
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

    public function markAsConfirmed($id, Request $request)
    {

        DB::beginTransaction();
        try {
            $bonretourVente = bonretourVente::find($id);

            if(!$bonretourVente) {
                return response()->json([
                    'message' => 'Bon de Retour introuvable'
                ], 400);
            }

            if($bonretourVente->Confirme == true) {
                return response()->json([
                    'message' => 'Bon de Retour est déjà Confirmé'
                ], 400);
            }

            $Articles =   bonretourVenteArticle::where('bonretourVente_id', $bonretourVente->id)->get();

            foreach($Articles as $article) {
                Inventory::updateOrCreate(
                    ['article_id' => $article['article_id'], 'warehouse_id' => $bonretourVente->warehouse_id],
                    ['actual_stock' => DB::raw('actual_stock + ' . $article['Quantity'])]
                );
            }

            $bonretourVente->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json(['message' => 'Bon de retour confirmè avec succès'], 200);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 404);
        }

    }

    public function getBonLivraison()
    {
        try {
            $linkedBonLivraison = bonretourVente::pluck('bonLivraison_id')->toArray();
            $bonLivraison = bonLivraisonVente::where('Confirme', 1)
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

    public function show($id)
    {
        try {

            $bonretourAchat = bonretourVente::find($id);
            if(!$bonretourAchat) {
                return response()->json([
                    'message' => 'Bon de Retour introuvable'
                ], 404);
            }

            $detailsCommande = bonretourVenteArticle::where('bonretourVente_id', $bonretourAchat->id)->get();

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

            $bonretourVente = bonretourVente::join('clients', 'bonretour_ventes.client_id', '=', 'clients.id')
            ->join('bon_livraison_ventes', 'bonretour_ventes.bonLivraison_id', '=', 'bon_livraison_ventes.id')
            ->join('warehouses', 'bonretour_ventes.warehouse_id', '=', 'warehouses.id')
            ->select('bonretour_ventes.*', 'clients.nom_Client', 'warehouses.nom_Warehouse', 'bon_livraison_ventes.Numero_bonLivraisonVente', 'bon_livraison_ventes.date_BlivraisonVente')
            ->where('bonretour_ventes.id', $id)
            ->first();

            $bonretourVenteArray = $bonretourVente->toArray();
            $bonretourVenteArray['Articles'] = $articles;

            return response()->json($bonretourVenteArray);

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
            $bonretourFounded = bonretourVente::find($id);

            if (!$bonretourFounded) {
                return response()->json([
                    'message' => 'Le bon de retour introuvable'
                ], 404);
            }

            if($bonretourFounded->Confirme==true) {
                return response()->json([
                    'message' => 'Le bon de retour est Confirmé, ne peut pas être supprimé'
                ], 400);
            }

            bonretourVenteArticle::where('bonretourVente_id', $bonretourFounded->id)->delete();
            $bonretourFounded->delete();

            DB::commit();
            return response()->json([
                'message' => 'Le bon de retour n`est plus disponible',
                'id' => $bonretourFounded->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }
}
