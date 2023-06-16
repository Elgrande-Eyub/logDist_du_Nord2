<?php

namespace App\Http\Controllers;

use App\Events\AlertStockProcessed;
use App\Http\Resources\invetoriesResource;
use App\Models\Article;
use App\Models\Inventory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index()
    {

        try {
            $Inventory = Inventory::join('articles', 'inventories.article_id', '=', 'articles.id')
            ->leftJoin('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->select('inventories.*', 'articles.article_libelle','articles.reference', 'warehouses.nom_Warehouse')
            ->get();
            return $Inventory;
        } catch(Exception $e) {
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);

        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            if (!$request->filled(['article_id', 'warehouse_id', 'actual_stock'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }


            $foundArticle = Inventory::where('article_id', $request->article_id)
                            ->where('warehouse_id', $request->warehouse_id)->first();

            if($foundArticle) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Produit déjà existant dans le stock'
                 ], 400);
            }

            if($request['actual_stock'] <= 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'le stock doit être supérieur à 0 qte'
                 ], 400);
            }


            $added = Inventory::create([
                'article_id'=>$request->article_id,
                'warehouse_id'=>$request->warehouse_id,
                'actual_stock'=>$request->actual_stock,
            ]);

            if(!$added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Produit non ajouté . un problème quelque part'
                 ], 400);
            }

            //send Email alert if the Stock has a problem
            $Article = Article::where('id',$request->article_id)->first();
            if($Article->alert_stock >= $request->actual_stock){
                event(new AlertStockProcessed());
            }

            DB::commit();

            return response()->json([
                'message' => 'Produit ajouté aux stocks avec succès'
            ]);


        } catch(Exception $e) {

            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 404);

        }
    }


    public function show($id)
    {
        try {
            // Find the article by ID
            $Inventory = Inventory::find($id);

            if (!$Inventory) {
                return response()->json([
                    'message' => 'Inventory not found'
                ], 404);
            }

            // Update the merchandise quantity


            return response()->json([
                'message' => 'Inventory requested',
                'Inventory' => $Inventory
            ]);

        } catch (Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            if (!$request->filled(['actual_stock'])) {
                return response()->json([
                    'message' => 'Veuillez remplir tous les champs obligatoires.'
                ], 400);
            }

            // $inventory = Inventory::find($id);
            /*  $inventory = Inventory::where('article_id',$request->article_id)
             ->where('warehouse_id',$request->warehouse_id)->first(); */

            $inventory  = Inventory::find($id);

            if (!$inventory) {
                return response()->json([
                    'message' => 'Inventory not found'
                ], 404);
            }

            $inventory->actual_stock = $request->actual_stock;
            $inventory->save();

            DB::commit();

             //send Email alert if the Stock has a problem
             $Article = Article::where('id',$inventory->article_id)->first();
             if($Article->alert_stock >= $inventory->actual_stock){
                 event(new AlertStockProcessed());
             }

            return response()->json([
                'message' => 'Inventaire mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 500);
        }
    }



    public function destroy($id)
    {
        try {

            $inventory = Inventory::find($id);

            if (!$inventory) {
                return response()->json([
                    'message' => 'Inventory not found 1'
                ], 404);
            }

            $inventory->delete();

            DB::commit();

            return response()->json([
                'message' => 'Inventory deleted successfully'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }
}
