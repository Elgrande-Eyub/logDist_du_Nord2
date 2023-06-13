<?php

namespace App\Http\Controllers;

use App\Events\AlertStockProcessed;
use App\Http\Resources\articleResource;
use App\Mail\AlerStockChecker;
use App\Models\Article;
use App\Models\Fournisseur;
use App\Models\Inventory;
use App\Models\Secteur;
use App\Models\warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ArticleController extends Controller
{
    // This function returns all categories
    public function index()
    {
        //  try {

            $Articles = Article::join('fournisseurs','articles.fournisseur_id','=','fournisseurs.id')
            ->leftjoin('article_categories','articles.category_id','=','article_categories.id')
            ->select('articles.*',
            'fournisseurs.fournisseur',
            'fournisseurs.id as fournisseur_id',
            'article_categories.id as category_id',
            'article_categories.category'
            )->get();

            /*  Mail::to('ayoub.baraoui.02@gmail.com')
             ->send(new AlerStockChecker()); */
            // event(new AlertStockProcessed());

            return response()->json(['data'=>  $Articles]);

       /*   } catch (Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        } */
    }

    public function insertArticles(Request $request)
    {
        $data = $request->all();

        foreach ($data as $item) {
            Article::create([
                'article_libelle' => $item['article_libelle'],
                'reference' => $item['reference'],
                'prix_unitaire' => $item['prix_unitaire'],
                'prix_public' => $item['prix_public'],
                'client_Fedele' => $item['client_Fedele'],
                'demi_grossiste' => $item['demi_grossiste'],
                'unite' => $item['unite'],
                'alert_stock' => $item['alert_stock'],
                'category_id' => $item['category_id'],
                'fournisseur_id' => $item['fournisseur_id'],
                'prix_achat' => $item['prix_achat'],
            ]);
        }

        return response()->json(['message' => 'Création réussie de L`Articles']);
    }

    public function articleFr($id)
    {
        try {

            $found = Fournisseur::where('id', $id)->exists();
            if (!$found) {
                return response()->json([
                    'message' => 'Fournisseur introuvable'
                ], 400);
            }

            $articleFr = Article::where('fournisseur_id', $id)->get();

            return response()->json([
                'articles' =>  $articleFr
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function articleWarehouse($id)
    {
         try {

            $secteur = Secteur::where('id', $id)->first();
            if (!$secteur) {
                return response()->json([
                    'message' => 'Secteur introuvable'
                ], 400);
            }

            $articleWarehouses = Inventory::where('warehouse_id', $secteur->warehouseDistrubtion_id)->get();
            $articlesDetails = [];

            foreach($articleWarehouses as $Article){
                $article = Article::find($Article->article_id);

                if ($article) {
                    $articlesDetails[] = $article;
                }
            }

            return response()->json($articlesDetails, 200);

         } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'article_libelle' => 'required',
                'reference' => 'required',
                'prix_unitaire' => 'required',
                'fournisseur_id' => 'required',
                'unite' => 'required',
                // 'prix_public' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }


            $found = Article::where('reference', $request->reference)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'L’article ne peut pas être dupliqué'
                ], 400);
            }


            $Added = Article::create([
                'article_libelle' => $request->article_libelle,
                'reference' => $request->reference,
                'prix_unitaire' => $request->prix_unitaire,
                'prix_public' => $request->prix_public,
                'prix_achat' => $request->prix_achat,
                'client_Fedele' => $request->client_Fedele,
                'demi_grossiste' => $request->demi_grossiste,
                'unite' => $request->unite,
                'category_id' => $request->category_id,
                'fournisseur_id' => $request->fournisseur_id,
                'alert_stock' => $request->alert_stock,

            ]);

            if (!$Added) {
                Log::error('Failed to create Article');
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
                ], 400);
            }

            return response()->json([
                'message' => 'Article créé avec succès',
                'id' => $Added->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function show($id)
    {
        try {

            $FoundedArticle = Article::find($id);

            if (!$FoundedArticle) {
                return response()->json([
                    'message' => 'L`Article introuvable'
                ], 404);
            }

            $Articles = Article::leftjoin('fournisseurs','articles.fournisseur_id','=','fournisseurs.id')
            ->leftjoin('article_categories','articles.category_id','=','article_categories.id')
            ->select('articles.*',
            'fournisseurs.fournisseur',
            'fournisseurs.id as fournisseur_id',
            'article_categories.id as category_id',
            'article_categories.category')
            ->where('articles.id',$id)
            ->first();

            return response()->json(['Article Requested'=>  $Articles]);


        } catch (Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'article_libelle' => 'required',
                'reference' => 'required',
                'prix_unitaire' => 'required',
                'fournisseur_id' => 'required',
                'unite' => 'required',
                // 'prix_public' => 'required',
            ]);

            if (!$validator->passes()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $ArticleFounded = Article::find($id);

            if (!$ArticleFounded) {
                return response()->json([
                    'message' => 'Article introuvable'
                ], 404);
            }

            $ArticleFounded->article_libelle = $request->article_libelle;
            $ArticleFounded->reference = $request->reference;
            $ArticleFounded->prix_unitaire = $request->prix_unitaire;
            $ArticleFounded->prix_public = $request->prix_public;
            $ArticleFounded->prix_achat = $request->prix_achat;
            $ArticleFounded->client_Fedele = $request->client_Fedele;
            $ArticleFounded->demi_grossiste = $request->demi_grossiste;
            $ArticleFounded->unite = $request->unite;
            $ArticleFounded->category_id = $request->category_id;
            $ArticleFounded->fournisseur_id = $request->fournisseur_id;

            $ArticleFounded->save();

            return response()->json([
                'message' => 'Article mis à jour avec succès',
                'id' => $ArticleFounded->id
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $ArticleFounded = Article::find($id);

            if (!$ArticleFounded) {
                return response()->json([
                    'message' => 'Article introuvable'
                ], 404);
            }

            $ArticleFounded->delete();

            return response()->json([
                'message' => 'Article supprimé avec succès',
                'id' => $ArticleFounded->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }
}
