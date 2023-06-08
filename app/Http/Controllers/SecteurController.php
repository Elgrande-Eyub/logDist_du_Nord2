<?php

namespace App\Http\Controllers;

use App\Http\Resources\secteurResource;
use App\Models\Secteur;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SecteurController extends Controller
{
    public function index()
    {
        try {
            $secteurs = Secteur::join('warehouses','secteurs.warehouseDistrubtion_id','=','warehouses.id')
            ->select('secteurs.*','warehouses.nom_Warehouse')
            ->get();

            return response()->json($secteurs);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 400);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'secteur' => 'required',
                'warehouseDistrubtion_id' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $existingSecteur = Secteur::where('secteur', $request->secteur)->exists();

            if ($existingSecteur) {
                return response()->json([
                    'message' => 'Secteur existe déjà'
                ], 400);
            }

            $secteur = Secteur::create([
                'secteur'=> $request->secteur,
                'warehouseDistrubtion_id' => $request->warehouseDistrubtion_id
            ]);

            if (!$secteur) {
                return response()->json([
                    'message' => 'Secteur non enregistré en raison d’une erreur'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Secteur ajouté avec succès',
                'id' => $secteur->id
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
            ], 400);
        }
    }

    public function show($id)
    {

        try {

            $founded = Secteur::find($id);

            if(!$founded) {
                return response()->json([
                    'message' => 'Secteur introuvable
                    '
                ], 400);
            }

            $secteur = Secteur::join('warehouses','secteurs.warehouseDistrubtion_id','=','warehouses.id')
            ->select('secteurs.*','warehouses.nom_Warehouse')
            ->where('secteurs.id',$id)
            ->first();


            return response()->json([
                'secteur' => $secteur
            ], 200);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }




    public function update(Request $request, $id)
    {
        DB::beginTransaction();


        try {

            $validator = Validator::make($request->all(), [
                'secteur' => 'required',
                'warehouseDistrubtion_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $Secteur = Secteur::find($id);

            if (!$Secteur) {
                return response()->json([
                    'message' => 'Secteur introuvable'
                ], 404);
            }

            $existingSecteur = Secteur::where('secteur', $request->secteur)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingSecteur) {
                return response()->json([
                    'message' => 'Secteur est déjà existe'
                ], 400);
            }

            $Secteur->update([
                'secteur' => $request->secteur,
                'warehouseDistrubtion_id' => $request->warehouseDistrubtion_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Secteur updated successfully',
                'id' => $Secteur->id
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }



public function destroy($id)
{
    DB::beginTransaction();

    try {
        $Secteur = Secteur::find($id);

        if (!$Secteur) {
            return response()->json([
                'message' => 'Secteur introuvable'
            ], 404);
        }

        $Secteur->delete();

        DB::commit();

        return response()->json([
            'message' => 'Secteur n`est plus disponible
            '
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong. Please try again later.'
        ], 400);
    }
}
}
