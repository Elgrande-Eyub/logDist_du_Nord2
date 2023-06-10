<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\avoirsVente;
use App\Models\avoirsVenteArticle;
use App\Models\BankAccount;
use App\Models\bonretourVenteArticle;
use App\Models\client;
use App\Models\Company;
use App\Models\factureVente;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AvoirsVenteController extends Controller
{
    public function index()
    {
        try {

            $Avoirs = avoirsVente::join('facture_ventes', 'avoirs_ventes.factureVente_id', '=', 'facture_ventes.id')
            ->join('clients', 'avoirs_ventes.client_id', '=', 'clients.id')
            ->leftJoin('bonretour_ventes', 'bonretour_ventes.bonLivraison_id', '=', 'facture_ventes.bonLivraisonVente_id')
            ->select('avoirs_ventes.*', 'facture_ventes.numero_FactureVente', 'facture_ventes.id as factureVente_id', 'clients.nom_Client', 'bonretour_ventes.Numero_bonRetour', 'bonretour_ventes.id as bonRetourVente_id')
            ->get();

            return  response()->json($Avoirs);

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
                'numero_avoirsVente' => 'required',
                'factureVente_id' => 'required',
                'date_avoirs' => 'required',
                'Total_HT' => 'required',
                'Total_TVA' => 'required',
                'Total_TTC' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $facture = factureVente::find($request->factureVente_id);
            if (!$facture) {
                return response()->json([
                    'message' => 'La facture introuvable'
                ], 400);
            }

            $avoirsAchat = avoirsVente::where('numero_avoirsVente', $request->numero_avoirsVente)->exists();
            if ($avoirsAchat) {
                return response()->json([
                    'message' => 'L`Avoirs ne peut pas être dupliqué'
                ], 400);
            }

            $date = Carbon::parse($request->date_avoirs);

            $Added = avoirsVente::create([
                'numero_avoirsVente' => $request->numero_avoirsVente,
                'factureVente_id' => $request->factureVente_id,
                'client_id' => $facture->client_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('n'),
                'Confirme' => 0,
                'Commentaire' => $request->Commentaire,
                'conditionPaiement'=> $request->conditionPaiement,
                'raison'=> $request->raison,
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

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                avoirsVenteArticle::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
                   'avoirsVente_id' => $Added->id

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

    public function getFactures()
    {
        try {
            $linkedFacture = avoirsVente::pluck('factureVente_id')->toArray();
            $Facture = factureVente::where('Confirme', 1)
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

            $bonretour = factureVente::leftjoin('bon_livraison_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
                        ->leftJoin('bonretour_ventes', 'bon_livraison_ventes.id', '=', 'bonretour_ventes.bonLivraison_id')
                        ->select('bonretour_ventes.*')
                        ->where('facture_ventes.id', $id)
                        ->first();

            if($bonretour->id == null){
                return response()->json([
                    'message' => 'Cet Facture navrois pas un bon Retour'
                ], 404);

            }

            $articles = bonretourVenteArticle::where('bonretourVente_id', $bonretour->id)->get();
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

    public function getNumeroAvoirs()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = avoirsVente::latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->numero_avoirsVente, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->numero_avoirsVente, 3, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'A-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['num_av' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function markAsConfirmed($id)
    {
        DB::beginTransaction();
        try {
            $avoirs = avoirsVente::find($id);

            if(!$avoirs) {
                return response()->json([
                    'message' => 'L`Avoirs de vente introuvable'
                ], 400);
            }

            $avoirs->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'L`Avoirs  de vente se confirmè avec succès',
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

            $Avoirs = avoirsVente::find($id);

            if(!$Avoirs) {
                return   response()->json(['message','Avoirs introuvable'], 404);
            }

            $detailsfacture = avoirsVenteArticle::where('avoirsVente_id', $Avoirs->id)->get();

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

            $Avoirs = avoirsVente::withTrashed()->join('facture_ventes', 'avoirs_ventes.factureVente_id', '=', 'facture_ventes.id')
            ->join('clients', 'avoirs_ventes.client_id', '=', 'clients.id')
            ->leftJoin('bonretour_ventes', 'bonretour_ventes.bonLivraison_id', '=', 'facture_ventes.bonLivraisonVente_id')
            ->select('avoirs_ventes.*', 'facture_ventes.numero_FactureVente', 'facture_ventes.id as factureVente_id', 'clients.nom_Client', 'bonretour_ventes.Numero_bonRetour', 'bonretour_ventes.id as bonRetourVente_id')
            ->where('avoirs_ventes.id', $id)
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

            $avoirs = avoirsVente::find($id);

            if (!$avoirs) {
                return response()->json([
                    'message' => 'Avoirs introuvable'
                ], 404);
            }

            if($avoirs->Confirme == true) {
                return response()->json([
                    'message' => 'Avoirs est Confirmé, ne peut pas être supprimé'
                ], 400);
            }

            $avoirs->delete();

            avoirsVenteArticle::where('avoirsVente_id', $avoirs->id)->delete();

            DB::commit();

            return response()->json([
                'message' => 'LAvoirs n`est plus disponible',
                'id' => $avoirs->id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function avoirePrint($id, $isDownloaded)
    {
        try {
            $commande = avoirsVente::withTrashed()->join('facture_ventes', 'avoirs_ventes.factureVente_id', '=', 'facture_ventes.id')
        ->leftjoin('bon_livraison_ventes', 'facture_ventes.bonLivraisonVente_id', '=', 'bon_livraison_ventes.id')
        ->leftjoin('bon_commande_ventes', 'bon_livraison_ventes.bonCommandeVente_id', '=', 'bon_commande_ventes.id')
        ->join('clients', 'avoirs_ventes.client_id', '=', 'clients.id')
        ->leftJoin('bonretour_ventes', 'bonretour_ventes.bonLivraison_id', '=', 'facture_ventes.bonLivraisonVente_id')
        ->select(
            'avoirs_ventes.*',
            'bon_commande_ventes.id as bonCommandeVente_id',
            'bon_commande_ventes.Numero_bonCommandeVente',
            'bon_livraison_ventes.id as bonLivraisonVente_id',
            'bon_livraison_ventes.Numero_bonLivraisonVente',
            'bonretour_ventes.id as bonRetourVente_id',
            'bonretour_ventes.Numero_bonRetour',
            'facture_ventes.numero_FactureVente',
            'clients.nom_Client',
            'bonretour_ventes.Numero_bonRetour',
            'bonretour_ventes.id as bonRetourVente_id'
        )
        ->where('avoirs_ventes.id', $id)
        ->first();

            $bank = BankAccount::get()->first();

            $articles = avoirsVenteArticle::withTrashed()->select('avoirs_vente_articles.*', 'articles.*')
              ->join('articles', 'avoirs_vente_articles.article_id', '=', 'articles.id')
              ->where('avoirsVente_id', $id)
              ->get();

            $client = client::withTrashed()->find($commande->client_id);

            $company = Company::get()->first();

            $pdf = app('dompdf.wrapper');

            $contxt = stream_context_create([
              'ssl' => [
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true,
              ]
      ]);

            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->setHttpContext($contxt);

            $pdf->loadView('Prints.vente.AvoirsVente', compact('commande', 'articles', 'client', 'bank', 'company', 'pdf'));

            if($isDownloaded === 'true') {
                return $pdf->download('Facture_Nº'.$commande->numero_Facture.'.pdf');
            }

            return $pdf->stream('Facture_'.$commande->numero_Facture.'.pdf');

        } catch (Exception $e) {
            abort(404);

        }
    }
}
