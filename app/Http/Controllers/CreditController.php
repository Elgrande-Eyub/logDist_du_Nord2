<?php

namespace App\Http\Controllers;

use App\Models\credit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditController extends Controller
{
    public function index()
    {
        try {

            $credits = credit::leftJoin('clients', 'credits.client_id', '=', 'clients.id')
            ->leftJoin('fournisseurs', 'credits.fournisseur_id', '=', 'fournisseurs.id')
            ->select('credits.*', 'fournisseurs.fournisseur', 'clients.nom_Client')
            ->get();

            return response()->json($credits);

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
                'montant' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            if($request->fournisseur_id == null &&  $request->client_id == null) {
                return response()->json([
                    'message' => 'il faut choisir le fournisseur ou le client'
                ], 400);
            }

            if($request->montant  <= 0) {
                return response()->json([
                    'message' => 'le montant doit être supérieure à 0'
                ], 400);
            }

            $Added = credit::create([
                'EtatPaiement' => $request->EtatPaiement,
                'Commentaire' => $request->Commentaire,
                'Confirme' => $request->Confirme,
                'montant' => $request->montant,
                'Total_Regler' => 0,
                'Total_Rester' => $request->montant,
                'fournisseur_id' => $request->fournisseur_id,
                'client_id' => $request->client_id,
            ]);

            if (!$Added) {
                return response()->json([
                    'message' => 'Credit non enregistré en raison d’une erreur'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Création réussie de Credit',
                'id' => $Added->id,
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);
        }
    }

    public function markAsConfirmed($id)
    {
        DB::beginTransaction();
        try {
            $credit = credit::find($id);

            if(!$credit) {
                return response()->json([
                    'message' => 'La credit introuvable'
                ], 400);
            }

            $credit->update([
                'Confirme' => true,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'La credit se confirmè avec succès',
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getCreditFournisseurs()
    {
        try {

            $CreditFournisseur = credit::leftJoin('fournisseurs', 'credits.fournisseur_id', '=', 'fournisseurs.id')
            ->whereNotNull('credits.fournisseur_id')
            ->where('credits.client_id', '=', null)
            ->select('credits.*', 'fournisseurs.fournisseur')
            ->get();

            return response()->json($CreditFournisseur);

        } catch(Exception $e) {
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getCreditClients()
    {
        try {
            $CreditClient = credit::leftJoin('clients', 'credits.client_id', '=', 'clients.id')
                ->whereNotNull('credits.client_id')
                ->where('credits.fournisseur_id', '=', null)
                ->select('credits.*', 'clients.nom_Client')
                ->get();

            return response()->json($CreditClient);
        } catch(Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function getCreditFournisseur($id)
    {
        try {

            $credit = credit::find($id);

            if (!$credit) {
                return response()->json([
                    'message' => 'Credit introuvable'
                ], 404);
            }

            $CreditFournisseur = credit::leftJoin('fournisseurs', 'credits.fournisseur_id', '=', 'fournisseurs.id')
            ->whereNotNull('credits.fournisseur_id')
            ->where('credits.client_id', '=', null)
            ->select('credits.*', 'fournisseurs.*')
            ->where('credits.id',$id)
            ->first();

            return response()->json($CreditFournisseur);

        } catch(Exception $e) {
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

    public function getCreditClient($id)
    {
        try {
            $credit = credit::find($id);

            if (!$credit) {
                return response()->json([
                    'message' => 'Credit introuvable'
                ], 404);
            }

            $CreditClient = credit::leftJoin('clients', 'credits.client_id', '=', 'clients.id')
                ->whereNotNull('credits.client_id')
                ->where('credits.fournisseur_id', '=', null)
                ->select('credits.*', 'clients.*')
                ->where('credits.id',$id)
                ->first();

            return response()->json($CreditClient);
        } catch(Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }
    public function show($id)
    {
        try {
            $credit = credit::find($id);

            if (!$credit) {
                return response()->json([
                    'message' => 'Credit introuvable'
                ], 404);
            }

            $credit = credit::leftJoin('clients', 'credits.client_id', '=', 'clients.id')
            ->leftJoin('fournisseurs', 'credits.fournisseur_id', '=', 'fournisseurs.id')
            ->select('credits.*', 'fournisseurs.fournisseur', 'clients.nom_Client')
            ->where('credits.id',$id)
            ->first();

            return response()->json($credit);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement
                '
            ], 400);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $credit = credit::find($id);

            if (!$credit) {
                return response()->json([
                    'message' => 'credit introuvable'
                ], 404);
            }

            if($credit->Confirme == true) {
                return response()->json([
                    'message' => 'credit est Confirmé, ne peut pas être supprimé'
                ], 409);
            }

            $credit->delete();

            DB::commit();
            return response()->json([
                'message' => 'credit nest plus disponible',
                'id' => $credit->id
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }
}
