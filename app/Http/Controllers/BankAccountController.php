<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    public function index()
    {
        try {

            $banks = BankAccount::all();
            return response()->json($banks, 200);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);

        }
    }


    public function create()
    {

    }


    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'nomBank' => 'required|string',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string',
                'numero_compt' => 'required|string',
                'rib_compt' => 'required|string',
                // 'solde' => 'required|numeric',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()
                ], 400);
            }

            $existingBankAccount = BankAccount::first();

            if ($existingBankAccount) {
                return response()->json([
                    'message' => 'La compte Bancaire de Societe est déjà existe'
                ], 400);
            }

            $Added = BankAccount::create([
             'nomBank' => $request->nomBank,
             'adresse' => $request->adresse,
             'telephone' => $request->telephone,
             'numero_compt' => $request->numero_compt,
             'rib_compt' => $request->rib_compt,
             'solde' => 0,
             'Commentaire' => $request->Commentaire,
        ]);

            // Check if the Bank Account was successfully created
            if (!$Added) {
                DB::rollBack();
                Log::error('Failed to create bank');
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
                ], 400);
            }

            DB::commit();

            // Return a success message and the bank ID
            return response()->json([
                'message' => 'bank Account created successfully',
                'id' => $Added->id
            ]);



        } catch(Exception $e) {

            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function show($id)
    {
        try {

            $BankAccount = BankAccount::find($id);
            if(!$BankAccount) {
                return response()->json([
                    'message' => 'BankAccount not found'
                ], 404);
            }


            return response()->json($BankAccount);


        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function edit(BankAccount $bankAccount)
    {

    }


    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'nomBank' => 'required|string',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string',
            'numero_compt' => 'required|string',
            'rib_compt' => 'required|string',
            // 'solde' => 'required|numeric',
            'Commentaire' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 400);
        }

        try {
            $BankAccountFounded = BankAccount::find($id);

            if(!$BankAccountFounded) {
                return response()->json([
                    'message' => 'Bon Commande not found'
                ], 400);
            }

            $BankAccountFounded->update([

             'nomBank' => $request->nomBank,
             'adresse' => $request->adresse,
             'telephone' => $request->telephone,
             'numero_compt' => $request->numero_compt,
             'rib_compt' => $request->rib_compt,
             'Commentaire' => $request->Commentaire,
            ]);

            return response()->json([
               'message' => 'BankAccount updated successfully.',
               'BankAccount_id' => $id
           ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $BankAccountFounded = BankAccount::find($id);

            if(!$BankAccountFounded) {
                return response()->json([
                    'message' => 'BankAccount not found'
                ], 400);
            }

            $BankAccountFounded->delete();

            return response()->json([
               'message' => 'BankAccount deleted successfully.',
               'BankAccount_id' => $id,
               200
           ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }
}
