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
            ->leftjoin('fournisseurs', 'bon_commandes.fournisseur_id', '=', 'fournisseurs.id')
            ->select('bon_commandes.*', 'fournisseurs.fournisseur')
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

            $lastRecord = bonCommande::withTrashed()->latest()->first();

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

            $found = bonCommande::where('Numero_bonCommande', $request->Numero_bonCommande)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'Bon Commande Already Exists'
                ], 409);
            }

            $date = Carbon::parse($request->date_BCommande);
            $Etat = $request->Confirme ? 'Recu' : 'Saisi';

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

            if (!$Added) {

                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
                ], 400);
            }

            foreach($request->Articles as $article) {

                if($article['Quantity'] <= 0) {
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

            $bonCommande = bonCommande::withTrashed()->leftJoin('bon_livraisons', 'bon_commandes.id', '=', 'bon_livraisons.bonCommande_id')
            ->join('fournisseurs', 'bon_commandes.fournisseur_id', '=', 'fournisseurs.id')->withTrashed()
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

            if($bonCommande->Confirme){
                return response()->json([
                    'message' => 'Bon de Commande est déjà Confirmé'
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
            $BCommandeFounded = bonCommande::find($id);

            if (!$BCommandeFounded) {
                return response()->json([
                    'message' => 'Bon de Commande introuvable'
                ], 404);
            }

            if($BCommandeFounded->Confirme == true) {
                return response()->json([
                    'message' => 'bon Commande est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            bonCommande_article::where('bonCommande_id', $BCommandeFounded->id)->forceDelete();
            $BCommandeFounded->forceDelete();

            DB::commit();
            return response()->json([
                'message' => 'Bon de Commande n`est plus disponible',
                'id' => $BCommandeFounded->id

            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    }


    public function printbonCommande($id, $condition, $isDownloaded)
    {
        try {

            $commande = bonCommande::withTrashed()->find($id);
            $articles = bonCommande_article::withTrashed()->select('bon_commande_articles.*', 'articles.*')
                ->join('articles', 'bon_commande_articles.article_id', '=', 'articles.id')
                ->where('bonCommande_id', $id)

                ->get();

            $fournisseur = Fournisseur::withTrashed()->find($commande->fournisseur_id);

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

            $pdf->loadView('Prints.bonCommande', compact('commande', 'articles', 'fournisseur', 'bank', 'company', 'condition', 'pdf'));

            if($isDownloaded === 'true') {
                return $pdf->download('Bon_Commande_Nº'.$commande->Numero_bonCommande.'.pdf');
            }

            return $pdf->stream('Bon_Commande_'.$commande->Numero_bonCommande.'.pdf');

        } catch (Exception $e) {
            abort(404);
        }

    }
}
