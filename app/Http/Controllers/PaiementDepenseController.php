<?php

namespace App\Http\Controllers;

use App\Models\depense;
use App\Models\paiementDepense;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaiementDepenseController extends Controller
{

    public function index()
    {
        try {

            $Paiements = paiementDepense::join('depenses','paiement_depenses.depense_id','=','depenses.id')
            ->select('paiement_depenses.*','depenses.depense')
            ->get();

            return response()->json($Paiements);

        }catch(Exception $e) {
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
                'dateDepense' => 'required',
                'montantTotal' =>'required',
                'depense_id' => 'required',
                'attachement' => 'nullable|mimes:jpeg,png,jpg,pdf',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }


            $found = paiementDepense::where('numero_Depense', $request->numero_Depense)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'Le Paiement de depense ne peut pas être dupliqué'
                ], 400);
            }


            $Added = paiementDepense::create([
                'numero_Depense' => $request->numero_Depense,
                'EtatPaiement' => $request->EtatPaiement, // impaye , en cour , paye
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'dateDepense' =>  $request->dateDepense,
                'montantTotal' => $request->montantTotal,
                'remise' => $request->remise,
                'TVA' => $request->TVA,
                'Total_Rester' => $request->montantTotal,
                'depense_id' => $request->depense_id,
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
                Storage::disk('paiementDepense')->put($imageName, file_get_contents($image));
                $Added->update([
                    'attachement' => $imageName
                ]);
            }

            DB::commit();

            return response()->json([
               'message' => 'Le Paiement de Depense créée avec succès',
               'id' => $Added->id
            ]);

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
            $facture = paiementDepense::find($id);

            if(!$facture) {
                return response()->json([
                    'message' => 'Le Paiement de Depense introuvable'
                ], 400);
            }

            $facture->update([
                'Confirme' => true,
                'Etat' => 'Recu',
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Le Paiement de Depense se confirmè avec succès',
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

            $paiementDepense = paiementDepense::join('depenses','paiement_depenses.depense_id','=','depenses.id')
            ->select('paiement_depenses.*','depenses.depense')
            ->find($id);

            if(!$paiementDepense) {
                return   response()->json(['message','Paiement Depense introuvable'], 404);
            }

            return response()->json($paiementDepense);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {

            $paiementDepense = paiementDepense::find($id);

            if(!$paiementDepense) {
                return   response()->json(['message','Paiement Depense introuvable'], 404);
            }

            if($paiementDepense->Confirme == true) {
                return response()->json([
                    'message' => 'Paiement Depense est Confirmé, ne peut pas être supprimé'
                ], 400);
            }


            $paiementDepense->delete();


            DB::commit();

            return response()->json([
                'message' => 'Paiement Depense n`est plus disponible',
                'id' => $paiementDepense->id
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 400);
        }
    }
}
