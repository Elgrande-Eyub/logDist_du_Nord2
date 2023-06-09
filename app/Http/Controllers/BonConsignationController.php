<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\bonConsignation;
use App\Models\bonConsignationArticle;
use App\Models\facture;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BonConsignationController extends Controller
{

    public function index()
    {
        try {
        $bons =  bonConsignation::join('factures','bon_consignations.facture_id','=','factures.id')
        ->select('bon_consignations.*','factures.numero_Facture')
        ->get();
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
                'facture_id' => 'required',
                'numero_bonConsignation' => 'required',
                'Total_Emballages' => 'required',
                'attachement' => 'nullable|mimes:jpeg,pngnjpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = bonConsignation::where('numero_bonConsignation', $request->numero_bonConsignation)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Le Bon de Consignation ne peut pas être dupliqué'
                ], 400);
            }

            $facture = facture::where('id', $request->facture_id)->exists();
            if(!$facture) {
                return response()->json([
                    'message' => 'La Facture introuvable'
                ], 400);
            }

            $Added = bonConsignation::create([
                'facture_id' => $request->facture_id,
                'numero_bonConsignation' => $request->numero_bonConsignation,
                'Total_Emballages' => $request->Total_Emballages,
                'etat' =>   'dérouler', //$request->etat,
                'representant' =>$request->representant,
                'Commentaire' =>$request->Commentaire,
                'transporteur' =>$request->transporteur,
                'matriculeCamion' =>$request->matriculeCamion,
                'conditionPaiement' =>$request->conditionPaiement,

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
                Storage::disk('bonConsignation')->put($imageName, file_get_contents($image));
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

                bonConsignationArticle::create([
                   'bonConsignation_id' => $Added->id,
                   'reference' => $article['reference'],
                   'Quantity' => $article['Quantity'],
                   'article_libelle' => $article['article_libelle'],
                   'Prix_unitaire' => $article['Prix_unitaire'],
                   'Total' => $article['Total'],
               ]);

            }
            DB::commit();

            return response()->json([
                    'message' => 'Création réussie de bon de Consignation',
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

            $bonConsignation = bonConsignation::find($id);
            if(!$bonConsignation) {
                return response()->json([
                    'message' => 'Bon Commande introuvable'
                ], 404);
            }

            $detailsCommande = bonConsignationArticle::where('bonConsignation_id', $bonConsignation->id)->withTrashed()->get();

            $articles = [];

            foreach($detailsCommande as $detail) {


                $article = [
                    'reference' => $detail->reference,
                    'article_libelle' => $detail->article_libelle,
                    'Prix_unitaire' => $detail->Prix_unitaire,
                    'Quantity' => $detail->Quantity,
                    'Total' => $detail->Total,
                ];
                $articles[] = $article;
            }

            $bonConsignation = bonConsignation::withTrashed()->join('factures','bon_consignations.facture_id','=','factures.id')
            ->select('bon_consignations.*','factures.numero_Facture')
            ->where('bon_consignations.id',$id)
            ->first();

            $bonConsignationArray = $bonConsignation->toArray();
            $bonConsignationArray['Articles'] = $articles;

            return response()->json($bonConsignationArray);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function destroy(bonConsignation $bonConsignation)
    {

    }
}
