<?php

namespace App\Http\Controllers;

use App\Models\warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function index()
    {
        try {
            $warehouses = Warehouse::all();
            return response()->json($warehouses);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'nom_Warehouse' => 'required',
                'city' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $founded = Warehouse::where('nom_Warehouse', $request->nom_Warehouse)->exists();

            if ($founded) {
                return response()->json([
                    'message' => 'Warehouse already Exists'
                ], 400);
            }

            $warehouse = Warehouse::create([
                'nom_Warehouse' => $request->nom_Warehouse,
                'city' => $request->city,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
            ]);

            if (!$warehouse) {
                return response()->json([
                    'message' => 'Warehouse could not be added. Please try again later.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Warehouse added successfully',
                'id' => $warehouse->id,
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
            $warehouse = Warehouse::find($id);

            if (!$warehouse) {
                return response()->json([
                    'message' => 'Warehouse not found'
                ], 400);
            }

            return response()->json([
                'message' => 'Warehouse found',
                'warehouse' => $warehouse
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
                'nom_Warehouse' => 'required',
                'email' => 'email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $warehouse = Warehouse::find($id);

            if (!$warehouse) {
                return response()->json([
                    'message' => 'Warehouse not found'
                ], 404);
            }

            $warehouse->update([
                'nom_Warehouse' => $request->nom_Warehouse,
                'city' => $request->city,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'email' => $request->email,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Warehouse updated successfully',
                'id' => $warehouse->id
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
            $warehouse = Warehouse::find($id);

            if (!$warehouse) {
                return response()->json([
                    'message' => 'Warehouse not found'
                ], 404);
            }

            $warehouse->delete();

            DB::commit();

            return response()->json([
                'message' => 'Warehouse deleted successfully'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

}
