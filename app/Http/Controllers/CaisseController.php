<?php

namespace App\Http\Controllers;

use App\Models\Caisse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CaisseController extends Controller
{
    public function index()
    {
        try {
            $caisses = Caisse::all();
            return response()->json($caisses);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }

    }

    public function store(Request $request)
    {
        try {


            $validator = Validator::make($request->all(), [

                'commentaire' => 'nullable|string',
                'type' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $existingCaisse = Caisse::first();

            if ($existingCaisse) {
                return response()->json([
                    'message' => 'La caisse est déjà existe'
                ], 400);
            }



            $Added =  Caisse::create([
                'solde' => 0,
                'commentaire' => $request->commentaire,
                'type' => $request->type,
            ]);


            if(!$Added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Création réussie de La caisse',
                'Caisse' => $Added->id
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $caisse = Caisse::findOrFail($id);
            return response()->json($caisse);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }

    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [

                'commentaire' => 'nullable|string',
                'type' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $caisse = Caisse::findOrFail($id);

            $caisse->update([
                'commentaire' => $request->commentaire,
                'type' => $request->type,
            ]);

            return response()->json($caisse);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }
    }

    public function destroy($id)
    {
        try {
            $caisse = Caisse::find($id);

            if (!$caisse) {
                return response()->json([
                    'message' => 'La Caisse introuvable'
                ], 404);
            }
            $caisse->delete();
            return response()->json(['message' => `la Caisse n'est plus disponible`]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }
    }


}
