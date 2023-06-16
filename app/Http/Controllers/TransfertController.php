<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Camion;
use App\Models\Company;
use App\Models\employee;
use App\Models\Inventory;
use App\Models\Transfert;
use App\Models\transfertArticle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransfertController extends Controller
{
    public function index()
    {
        try {

            $Transfert = Transfert::join('warehouses as warehousesFrom', 'transferts.from', '=', 'warehousesFrom.id')
            ->join('warehouses as warehouseTo', 'transferts.to', '=', 'warehouseTo.id')
            ->leftjoin('camions', 'transferts.camion_id', '=', 'camions.id')
            ->leftjoin('employees', 'transferts.transporteur_id', '=', 'employees.id')
            ->select(
                'transferts.*',
                'warehousesFrom.nom_Warehouse as warehousesFrom',
                'warehouseTo.nom_Warehouse as warehouseTo',
                'employees.nom_employee',
                'camions.matricule',
                'camions.marque',
                'camions.modele',
                'camions.etat',
            )
            ->get();

            return response()->json($Transfert);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Check if fields are not empty
            $validator = Validator::make($request->all(), [
                'reference' => 'required',
                'from' => 'required',
                'to' => 'required',
                'dateTransfert'=> 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            // Check if the Bon Commande already exists
            $found = Transfert::where('reference', $request->reference)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'ce Operation Transfert est déjà exists'
                ], 409);
            }



            if($request->from == $request->to) {
                return response()->json([
                    'message' => 'Choisi another Entrepot'
                ], 409);
            }
            foreach($request->Articles as $article) {

                $CheckStock = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $request->from)
                ->first();

                $produit = Article::where('id', $article['article_id'])->first();
                if(!$CheckStock) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,

                    ], 404);
                }

                if($article['Quantity'] > $CheckStock->actual_stock) {
                    DB::rollBack();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'Quantity'=> $article['Quantity'],
                     'actual_stock' => $CheckStock->actual_stock
                ], 404);
                }
            }


            // Create the new Bon Commande
            $Added = Transfert::create([
                'reference' => $request->reference,
                'from' => $request->from,
                'to' => $request->to,
                'camion_id' => $request->camion_id,
                'transporteur_id' => $request->transporteur_id,
                'Commentaire' => $request->Commentaire,
                'dateTransfert' => Carbon::now(),
                'Confirme' => 0,
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

                if($article['Quantity'] <= 0) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'la quantité doit être supérieure à 0'
                        ], 404);
                }

                transfertArticle::create([
                   'article_id' => $article['article_id'],
                   'Quantity' => $article['Quantity'],
                   'transfert_id' => $Added->id
             ]);
            }

            DB::commit();

            // Return a success message and the new Bon Commande ID
            return response()->json([
                    'message' => 'Création réussie de Operation Transfert',
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

            $Transfert = Transfert::find($id);
            if(!$Transfert) {
                return response()->json([
                    'message' => 'Transfert Operation introuvable'
                ], 404);
            }

            $detailsCommande = transfertArticle::where('transfert_id', $Transfert->id)->get();

            $articles = [];

            foreach($detailsCommande as $detail) {

                $articl = Article::where('id', $detail->article_id)->first();
                $article = [
                    'article_id' => $detail->article_id,
                    'Quantity' => $detail->Quantity,
                    'reference' => $articl->reference,
                    'article_libelle' => $articl->article_libelle,
                    'unite' => $articl->unite,


                ];
                $articles[] = $article;
            }

            $Transfert = Transfert::join('warehouses as warehousesFrom', 'transferts.from', '=', 'warehousesFrom.id')
            ->join('warehouses as warehouseTo', 'transferts.to', '=', 'warehouseTo.id')
            ->leftjoin('camions', 'transferts.camion_id', '=', 'camions.id')
            ->leftjoin('employees', 'transferts.transporteur_id', '=', 'employees.id')
            ->select(
                'transferts.*',
                'warehousesFrom.nom_Warehouse as warehousesFrom',
                'warehouseTo.nom_Warehouse as warehouseTo',
                'employees.nom_employee',
                'camions.matricule',
                'camions.marque',
                'camions.modele',
                'camions.etat',
            )
            ->where('transferts.id', $id)
            ->first();

            $TransfertArray = $Transfert->toArray();
            $TransfertArray['Articles'] = $articles;

            return response()->json($TransfertArray);

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
            $Transfert = Transfert::find($id);

            if(!$Transfert) {
                return response()->json([
                    'message' => 'Cet Transfert introuvable'
                ], 400);
            }

            if($Transfert->Confirme == true) {
                return response()->json([
                    'message' => 'Transfert est déjà Confirmé'
                ], 400);
            }

            $Articles = transfertArticle::where('transfert_id', $Transfert->id)->get();

            foreach($Articles as $article) {

                $from = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $Transfert->from)
                ->first();

                $to = Inventory::where('article_id', $article['article_id'])
                ->where('warehouse_id', $Transfert->to)
                ->first();

                if(!$from) {
                    DB::rollBack();
                    return response()->json([
                       'message' => 'le produit est introuvable dans cet entrepôt'
                    ], 404);
                }



                if($article['Quantity'] > $from->actual_stock) {
                    DB::rollBack();
                    $produit = Article::where('id', $article['article_id'])->first();
                    return response()->json([
                     'message' => 'La Quantité requise non disponible a ce moment pour larticle R-'.$produit->reference.'::'.$produit->article_libelle,
                     'Quantity'=> $article['Quantity'],
                     'actual_stock' =>$from->actual_stock
                    ], 404);
                }

                $from->update([
                 'actual_stock' => $from->actual_stock - $article['Quantity'],
                ]);

                if(!$to) {
                    Inventory::create([
                        'actual_stock' =>  $article['Quantity'],
                        'warehouse_id' =>  $Transfert->to,
                        'article_id' =>  $article['article_id']
                    ]);

                } else {
                    $to->update([
                        'actual_stock' => $to->actual_stock + $article['Quantity'],
                       ]);
                }

            }

            $Transfert->update([
                'Confirme' => true,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Operation Transfert se confirme avec succès , et le stock a ete mise ajouré avec succès',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $Transfert_Founded = Transfert::find($id);


            if (!$Transfert_Founded) {
                return response()->json([
                    'message' => 'Bon Sortie introuvable'
                ], 404);
            }

            if($Transfert_Founded->Confirme == true) {
                return response()->json([
                    'message' => 'Operation Transfert est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            transfertArticle::where('transfert_id', $Transfert_Founded->id)->delete();
            $Transfert_Founded->delete();

            DB::commit();
            return response()->json([
                'message' => 'Operation Transfert nest plus disponible',
                'id' => $Transfert_Founded->id

            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function getInventoryBywarehouse($id){
       try{

        $inventoryArticles = Inventory::join('articles','inventories.article_id','=','articles.id')
                        ->where('warehouse_id',$id)
                        ->select('inventories.*','articles.reference','articles.article_libelle')
                        ->get();

        return $inventoryArticles;

       }catch(Exception $e){

       }
    }


    public function getNumeroT()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = Transfert::latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->reference, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->reference, 2, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'T-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['num_Transfert' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function printt($id, $isDownloaded)
    {
        try {

            $commande = Transfert::join('warehouses as warehousesFrom', 'transferts.from', '=', 'warehousesFrom.id')
            ->join('warehouses as warehouseTo', 'transferts.to', '=', 'warehouseTo.id')
            ->select(
                'transferts.*',
                'warehousesFrom.nom_Warehouse as warehousesFrom',
                'warehouseTo.nom_Warehouse as warehouseTo',
            )
            ->find($id);

            if (!$commande) {
                return response()->json([
                    'message' => 'Ce Opertaion Transfert est introuvable'
                ], 409);
            }

            $articles = transfertArticle::join('articles', 'transfert_articles.article_id', '=', 'articles.id')
                ->select('transfert_articles.*', 'articles.reference', 'articles.article_libelle', 'articles.unite')
                ->where('transfert_id', $commande->id)
                ->get();

            $company = Company::get()->first();
            $employee = employee::find($commande->transporteur_id);

            $dateTirage = Carbon::now();

            $camion = Camion::where('id', $commande->camion_id)->first();

            $pdf = app('dompdf.wrapper');

            //############ Permitir ver imagenes si falla ################################
            $contxt = stream_context_create([
              'ssl' => [
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true,
              ]
            ]);
            $pdf->setPaper('A4', 'landscape');
            $pdf->getDomPDF()->setHttpContext($contxt);


            $pdf->loadView('Prints.vente.Transfert', compact('commande', 'articles', 'company', 'pdf', 'employee', 'dateTirage', 'camion'));

            if($isDownloaded === 'true') {
                return $pdf->download('Transfert_Nº'.$commande->reference.'.pdf');
            }

            return $pdf->stream('Transfert_Nº'.$commande->reference.'.pdf');


        } catch (Exception $e) {

            abort(404);
        }

    }
}
