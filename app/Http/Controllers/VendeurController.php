<?php

namespace App\Http\Controllers;

use App\Models\Vendeur;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendeurController extends Controller
{
    public function index()
    {
        try {
            $vendeurs = Vendeur::all();
            return response()->json($vendeurs);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }
    public function multipleVendeurs(Request $request)
    {
        DB::beginTransaction();
        try {
            $vendeursData = $request->all();

            foreach ($vendeursData as $vendeurData) {
                $vendeur = Vendeur::create([
                    'nomComplet' => $vendeurData['nomComplet'],
                    'cin' => $vendeurData['cin'],
                    'dateEmbauche' => $vendeurData['dateEmbauche'],
                    'dateNaissance' => $vendeurData['dateNaissance'],
                    'telephone' => $vendeurData['telephone'],
                    'adresse' => $vendeurData['adresse']
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Vendeurs inserted successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$request->filled(['nomComplet', 'cin', 'dateEmbauche', 'dateNaissance', 'telephone', 'adresse'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $added = Vendeur::create([
                'nomComplet' => $request->nomComplet,
                'cin' => $request->cin,
                'dateEmbauche' => $request->dateEmbauche,
                'dateNaissance' => $request->dateNaissance,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
            ]);

            if (!$added) {
                return response()->json([
                    'message' => 'Vendeur not recorded due to an error'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Vendeur added successfully',
                'id' => $added->id
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
            $vendeur = Vendeur::find($id);

            if (!$vendeur) {
                return response()->json([
                    'message' => 'Vendeur not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Vendeur found',
                'vendeur' => $vendeur
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
            if (!$request->filled(['nomComplet', 'cin', 'dateEmbauche', 'dateNaissance', 'telephone', 'adresse'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $vendeur = Vendeur::find($id);

            if (!$vendeur) {
                return response()->json([
                    'message' => 'Vendeur not found'
                ], 404);
            }

            $vendeur->update([
                'nomComplet' => $request->nomComplet,
                'cin' => $request->cin,
                'dateEmbauche' => $request->dateEmbauche,
                'dateNaissance' => $request->dateNaissance,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
            ]);

            DB::commit();

            return response()->json([     'message' => 'Vendeur updated successfully',
            'id' => $vendeur->id
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
        $vendeur = Vendeur::find($id);

        if (!$vendeur) {
            return response()->json([
                'message' => 'Vendeur not found'
            ], 404);
        }

        $vendeur->delete();

        DB::commit();

        return response()->json([
            'message' => 'Vendeur deleted successfully'
        ], 200);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong. Please try again later.'
        ], 400);
    }
}



}
