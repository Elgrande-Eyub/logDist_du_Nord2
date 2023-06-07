<?php

namespace App\Http\Controllers;

use App\Http\Resources\bonCommandeResource;
use App\Models\Article;
use App\Models\BankAccount;
use App\Models\bonCommande;
use App\Models\bonCommande_article;
use App\Models\Company;
use App\Models\Fournisseur;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\Extension\CommonMark\Parser\Inline\BangParser;
use PhpParser\Node\Stmt\Catch_;

class BonCommandeController extends Controller
{
    public function index()
    {
        try {

            $bonCommande = bonCommande::orderByDesc('Numero_bonCommande')
            ->leftjoin('fournisseurs','bon_commandes.fournisseur_id','=','fournisseurs.id')
            ->select('bon_commandes.*','fournisseurs.fournisseur')
            ->get();


            return response()->json(['data'=>$bonCommande]);

        } catch(Exception $e) {

            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);

        }

    }


    public function getNumeroBC()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = bonCommande::latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->Numero_bonCommande, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->Numero_bonCommande, 2, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'C-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['Numero_bonCommande' => $incrementString]);
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
            // Check if fields are not empty
            $validator = Validator::make($request->all(), [
                'fournisseur_id' => 'required',
                'Numero_bonCommande' => 'required',
                'date_BCommande' => 'required',
                'Confirme' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }



            // Check if the Bon Commande already exists
            $found = bonCommande::where('Numero_bonCommande', $request->Numero_bonCommande)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'Bon Commande Already Exists'
                ], 409);
            }



            // Parse Date to get Month and years of the Bon Commande
            $date = Carbon::parse($request->date_BCommande);
            // if the Commande is Confirmed then Status of the commande is Recu Otherwise is Saisi
            $Etat = $request->Confirme ? 'Recu' : 'Saisi';

            // Create the new Bon Commande
            $Added = bonCommande::create([
                'Numero_bonCommande' => $request->Numero_bonCommande,
                'fournisseur_id' => $request->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' => $date->format('n'),
                'Etat' => $Etat, // saiser ,en cours,annule, Recus
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_BCommande' => $request->date_BCommande,
                'Total_HT' => $request->Total_HT,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
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

                if($article['Quantity'] <= 0 ){
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                bonCommande_article::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total_HT' => $article['Total_HT'],
                   'bonCommande_id' => $Added->id
               ]);
            }

            DB::commit();

            // Return a success message and the new Bon Commande ID
            return response()->json([
                    'message' => 'Création réussie de Bon Commande',
                    'id' => $Added->id
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



            $bonCommande = bonCommande::find($id);
            if(!$bonCommande) {
                return response()->json([
                    'message' => 'Bon Commande introuvable'
                ], 404);
            }

            $detailsCommande = bonCommande_article::where('bonCommande_id', $bonCommande->id)->withTrashed()->get();

            $articles = [];

            foreach($detailsCommande as $detail) {

                $articl = Article::withTrashed()->find($detail->article_id);


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

            /* $bonCommande = bonCommande::join('fournisseurs', 'bon_commandes.fournisseur_id', '=', 'fournisseurs.id')
            ->select('bon_commandes.*', 'fournisseurs.fournisseur')
            ->where('bon_commandes.id', $id)
            ->first(); */
            $bonCommande = bonCommande::leftJoin('bon_livraisons', 'bon_commandes.id', '=', 'bon_livraisons.bonCommande_id')
            ->join('fournisseurs', 'bon_commandes.fournisseur_id', '=', 'fournisseurs.id')
            ->select('bon_commandes.*', 'fournisseurs.fournisseur', 'bon_livraisons.id as bonLivraison_id')
            ->where('bon_commandes.id', $id)

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



    /* public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $bonCommande = BonCommande::find($id);

            if (!$bonCommande) {
                return response()->json([
                    'message' => 'Bon Commande not found'
                ], 400);
            }

            // Remove existing bonCommande_articles
            bonCommande_article::where('bonCommande_id', $bonCommande->id)->delete();

            // Create new bonCommande_articles
            foreach ($request->Articles as $article) {
                bonCommande_article::create([
                    'article_id' => $article['article_id'],
                    'Quantity' => $article['Quantity'],
                    'Prix_unitaire' => $article['Prix_unitaire'],
                    'Total_HT' => $article['Total_HT'],
                    'bonCommande_id' => $id
                ]);
            }

            $date = Carbon::parse($request->date_BCommande);

            $Etat = $request->Confirme ? 'Recu' : 'saisi';

            $bonCommande->update([
                'Numero_bonCommande' => $request->Numero_bonCommande,
                'fournisseur_id' => $request->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' => $date->format('m'),
                'Confirme' => $request->Confirme,
                'Etat' => $Etat,
                'Commentaire' => $request->Commentaire,
                'date_BCommande' => $request->date_BCommande,
                'Total_HT' => $request->Total_HT,
                'TVA' => $request->TVA,
                'remise' => $request->remise,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Bon commande updated successfully.',
                'bonCommande_id' => $id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    } */

    public function markAsConfirmed($id)
    {

        DB::beginTransaction();
        try {
            $bonCommande = bonCommande::find($id);

            if(!$bonCommande) {
                return response()->json([
                    'message' => 'Bon de Commande introuvable'
                ], 400);
            }

            $bonCommande->update([
                'Confirme' => true,
                'Etat' => 'Recu',

            ]);
            DB::commit();
            return response()->json([
                'message' => 'Bon de Commande se confirmè avec succès',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }

    }

    public function destroy(bonCommande $bonCommande, $id)
    {
        DB::beginTransaction();
        try {
            // Find the Bon Comamnde  with the given ID
            $BCommandeFounded = bonCommande::find($id);

            // If the Bon Comamnde  doesn't exist, return an message
            if (!$BCommandeFounded) {
                return response()->json([
                    'message' => 'Bon de Commande introuvable'
                ], 404);
            }

            // delete articles related to  the bonCommande
            bonCommande_article::where('bonCommande_id', $BCommandeFounded->id)->delete();
            // delete bonCommande

            $BCommandeFounded->delete();
            DB::commit();
            // Return a success message with the deleted Article
            return response()->json([
                'message' => 'Bon de Commande n`est plus disponible',
                'id' => $BCommandeFounded->id

            ]);
        } catch (Exception $e) {
            // Return an message message for any other exceptions
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    }



    public function printbonCommande($id, $condition, $isDownloaded)
    {
        try {

            $commande = bonCommande::find($id);
            $articles = bonCommande_article::select('bon_commande_articles.*', 'articles.*')
                ->join('articles', 'bon_commande_articles.article_id', '=', 'articles.id')
                ->where('bonCommande_id', $id)

                ->get();

            $fournisseur = Fournisseur::find($commande->fournisseur_id);

            $company = Company::get()->first();
            $bank = BankAccount::get()->first();

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
                $pdf->loadView('Prints.bonCommande', compact('commande', 'articles', 'fournisseur', 'bank', 'company','condition', 'pdf'));

           /*  } elseif ($condition === 'sc') {
                $pdf->loadView('Prints.bonCommandeSC', compact('commande', 'articles', 'bank', 'fournisseur', 'company', 'pdf'));

            } */


            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Commande_Nº'.$commande->Numero_bonCommande.'.pdf');
            }

            return $pdf->stream('Bon_Commande_'.$commande->Numero_bonCommande.'.pdf');


        } catch (Exception $e) {

            abort(404);
        }

    }
}
