<?php

namespace App\Http\Controllers;

use App\Models\bonCommande;
use App\Models\bonCommande_article;
use App\Models\bonLivraison;
use App\Models\BonReception;
use App\Models\bonReception_article;
use App\Models\Inventory;
use App\Models\Fournisseur;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonReceptionController extends Controller
{
    public function index()
    {

        try {

            $bonReception = BonReception::all();
            return response()->json($bonReception);

        } catch(Exception $e) {


            return response()->json([
               'message' => 'Something went wrong. Please try again later.'
            ], 404);

        }



    }

/*     public function getBonLivraison(){
        try{
            $linkedBonLivraison = BonReception::pluck('bonLivraison_id')->toArray();
            $bonLivraison = bonLivraison::where('Confirme', 1)
            ->whereNotIn('id', $linkedBonLivraison)->get();

            return response()->json($bonLivraison);


         }catch(Exception $e){
                DB::rollBack();
                 return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                 ],404);
             }

    } */






    public function store(Request $request)
    {

        DB::beginTransaction();

        try {
            if (!$request->filled(['fournisseur_id','Numero_bonReception','Commentaire','date_BReception','Confirme','Total_HT','Total_TVA','Total_TTC'])) {
                return response()->json([
                    'message' => 'Please fill all fields required'
                ], 400);
            }

            $found = BonReception::where('Numero_bonReception', $request->Numero_bonReception)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Bon Reception cannot be duplicated'
                ], 400);
            }

            if($request->bonCommande_id) {
                $bonCommande = bonCommande::where('id', $request->bonCommande_id)->exists();
                if(!$bonCommande) {
                    return response()->json([
                        'message' => 'Bon Commande Entered not Found'
                    ], 400);
                }
            }

            $date = Carbon::parse($request->date_BReception);
            if($request->Confirme == true) {
                $Etat = 'Recu';
            } else {
                $Etat = 'saisi';
            }

            $Added = BonReception::create([
                'Numero_bonReception' => $request->Numero_bonReception,
                'bonCommande_id' => $request->bonCommande_id,
                'fournisseur_id' => $request->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' =>  $date->format('m'),
                'Etat' => $Etat,
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_BReception' => $request->date_BReception,
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
            ]);

            if (!$Added) {
                DB::rollBack();
                Log::error('Failed to create Bon Reception');
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                ], 400);
            }

            foreach($request->Articles as $article) {
                bonReception_article::create([
                   'bonReception_id' => $Added->id,
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'TVA' => $article['TVA'],
                   'Total_HT' => $article['Total_HT'],
                   'Total_TVA' => $article['Total_TVA'],
                   'Total_TTC' => $article['Total_TTC'],
               ]);

                if($request->Confirme == true) {
                    Inventory::updateOrCreate(
                        ['article_id' => $article['article_id']],
                        ['actual_stock' => DB::raw('actual_stock + ' . $article['Quantity'])]
                    );
                }
            }

            DB::commit();
            return response()->json([
                    'message' => 'Bon Recception created successfully',
                    'id' => $Added->id
                ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }

    }

    public function show(BonReception $bonReception, $id)
    {
        try {

            $bonReception = BonReception::find($id);
            if(!$bonReception) {
                return response()->json([
                    'message' => 'Bon Reception not found'
                ], 404);
            }

            $detailsCommande = bonReception_article::where('bonReception_id', $bonReception->id)->get();

            $articles = [];

            foreach($detailsCommande as $detail) {
                $article = [
                    'article_id' => $detail->article_id,
                    'Quantity' => $detail->Quantity,
                    'Prix_unitaire' => $detail->Prix_unitaire,
                    'TVA' => $detail->TVA,
                    'Total_HT' => $detail->Total_HT,
                    'Total_TVA' => $detail->Total_TVA,
                    'Total_TTC' => $detail->Total_TTC
                ];
                $articles[] = $article;
            }

            $bonReceptionArray = $bonReception->toArray();
            $bonReceptionArray['Articles'] = $articles;

            return response()->json($bonReceptionArray);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later'
            ], 404);
        }
    }

    public function update(Request $request, BonReception $bonReception, $id)
    {
        DB::beginTransaction();
        try {
            $bonReception = BonReception::find($id);

            if(!$bonReception) {
                return response()->json([
                    'message' => 'Bon Reception not found'
                ], 400);
            }

            if($bonReception->Confirme == true) {
                return response()->json([
                    'message' => 'Bon Reception Cannot be edited cause its already Confirmed'
                ], 400);
            }

            bonReception_article::Where('bonReception_id', $bonReception->id)->delete();

            foreach($request->Articles as $article) {

                bonReception_article::create([
                      'article_id' => $article['article_id'],
                      'Quantity' => $article['Quantity'],
                      'Prix_unitaire' => $article['Prix_unitaire'],
                      'TVA' => $article['TVA'],
                      'Total_HT' => $article['Total_HT'],
                      'Total_TVA' => $article['Total_TVA'],
                      'Total_TTC' => $article['Total_TTC'],
                      'bonReception_id' => $id
                  ]);

                Inventory::updateOrCreate(
                    ['article_id' => $article['article_id']],
                    ['actual_stock' => DB::raw('actual_stock + ' . $article['Quantity'])]
                );
            }

            $date = Carbon::parse($request->date_BReception);

            $bonReception->update([
                'Numero_bonReception' => $request->Numero_bonReception,
                'bonCommande_id' => $request->bonCommande_id,
                'fournisseur_id' => $request->fournisseur_id,
                'Exercice' => $date->format('Y'),
                'Mois' => $date->format('m'),
                'Confirme' => true,
                'Etat' => 'Recu',
                'Commentaire' => $request->Commentaire,
                'date_BReception' => $request->date_BReception,
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
            ]);

            DB::commit();
            return response()->json([
               'message' => 'Bon Reception updated successfully.',
               'bonReception_id' => $id
           ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }
    }

    public function markAsConfirmed($id)
    {
        try {
            $bonReception = BonReception::find($id);

            if(!$bonReception) {
                return response()->json([
                    'message' => 'Bon Reception not found'
                ], 400);
            }

            $bonReception->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            $Articles =   bonReception_article::where('bonReception_id', $bonReception->id)->get();

            foreach($Articles as $article) {

                Inventory::updateOrCreate(
                    ['article_id' => $article['article_id']],
                    ['actual_stock' => DB::raw('actual_stock + ' . $article['Quantity'])]
                );

            }

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }

    }

    public function destroy(BonReception $bonReception, $id)
    {
        DB::beginTransaction();
        try {
            $BReception_Founded = BonReception::find($id);
            if (!$BReception_Founded) {
                return response()->json([
                    'message' => 'Bon Reception not found'
                ], 404);
            }

            if($BReception_Founded->Confirme == true) {
                $BReception_Articles = bonReception_article::where('bonReception_id', $BReception_Founded->id)->get();
                foreach ($BReception_Articles as $article) {

                    $quantity = $article->Quantity;

                    Inventory::where('article_id', $article->article_id)
                        ->update(['actual_stock' => DB::raw('actual_stock - ' . $quantity)]);
                }

            }

            $BReception_Founded->delete();
            bonReception_article::where('bonReception_id', $BReception_Founded->id)->delete();

            return response()->json([
                'message' => 'Bon Reception deleted successfully',
                'id' => $BReception_Founded->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }
    }

    public function printbonReception($id)
    {
        $commande = bonReception::find($id);
        $articles = bonReception_article::select('bon_reception_articles.*', 'articles.*')
            ->join('articles', 'bon_reception_articles.article_id', '=', 'articles.id')
            ->where('bonReception_id', $id)
            ->get();
        $fournisseur = Fournisseur::find($commande->fournisseur_id);

        $pdf = FacadePdf::loadView('Prints.bonReceptionPre', compact(['commande','articles','fournisseur']));

        return $pdf->download('print.pdf');
    }
}
