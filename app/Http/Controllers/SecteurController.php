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

            // $validatedData = $request->validated();

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
}
