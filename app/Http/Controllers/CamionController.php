<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CamionController extends Controller
{
    public function index()
    {
        try {
            $camions = Camion::all();
            return response()->json($camions);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

    public function storeMultiple(Request $request)
    {
        DB::beginTransaction();
        try {
            $camions = $request->all();

            foreach ($camions as $camionData) {
                Camion::create([
                    'matricule' => $camionData['matricule'],
                    'marque' => $camionData['marque'],
                    'modele' => $camionData['modele'],
                    'annee' => $camionData['annee'],
                    'etat' => $camionData['etat'],
                    // 'km' => $camionData['km'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Camions inserted successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong. Please try again later.'], 400);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'matricule' => 'required',
                'marque' => 'required',
                'modele' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $existingCamion = Camion::where('matricule', $request->matricule)->exists();

            if ($existingCamion) {
                return response()->json([
                    'message' => 'Camion with the given matricule already exists.'
                ], 400);
            }

            $camion = Camion::create([
                'matricule' => $request->matricule,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'annee' => $request->annee,
                'etat' => $request->etat,
                // 'km' => $request->km,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Camion added successfully',
                'id' => $camion->id
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

    public function show($id)
    {
        try {
            $camion = Camion::find($id);

            if (!$camion) {
                return response()->json([
                    'message' => 'Camion not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Camion found',
                'camion' => $camion
            ], 200);
        } catch (Exception $e) {
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
                'matricule' => 'required',
                'marque' => 'required',
                'modele' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $camion = Camion::find($id);

            if (!$camion) {
                return response()->json([
                    'message' => 'Camion not found'
                ], 404);
            }

            $existingCamion = Camion::where('matricule', $request->matricule)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingCamion) {
                return response()->json([
                    'message' => 'Camion with the given matricule already exists.'
                ], 400);
            }

            $camion->update([
                'matricule' => $request->matricule,
                'marque' => $request->marque,
                'modele' => $request->modele,
                'annee' => $request->annee,
                'etat' => $request->etat,
                // 'km' => $request->km,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Camion updated successfully',
                'id' => $camion->id
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
            $camion = Camion::find($id);

            if (!$camion) {
                return response()->json([
                    'message' => 'Camion not found'
                ], 404);
            }

            $camion->delete();

            DB::commit();

            return response()->json([
                'message' => 'Camion deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }
}
