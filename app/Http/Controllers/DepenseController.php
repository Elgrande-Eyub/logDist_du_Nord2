<?php

namespace App\Http\Controllers;

use App\Models\depense;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepenseController extends Controller
{
    public function index()
    {
        try {
            $depenses = Depense::all();
            return response()->json($depenses);
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
                'depense' => 'required',
                'depense_Tax' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            if($request->depense_Tax < 0) {
                return response()->json([
                    'message' => 'TVA doit être egale ou supérieur à 0%'
                ], 400);
            }

            $existingDepense = Depense::where('depense', $request->depense)->exists();

            if ($existingDepense) {
                return response()->json([
                    'message' => 'Depense code already exists'
                ], 400);
            }

            $added = Depense::create([
                'depense' => $request->depense,
                'depense_Tax' => $request->depense_Tax,
            ]);

            if (!$added) {
                return response()->json([
                    'message' => 'Depense not recorded due to an error'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Depense added successfully',
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
            $depense = Depense::find($id);

            if (!$depense) {
                return response()->json([
                    'message' => 'Depense not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Depense found',
                'depense' => $depense
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
                'depense' => 'required',
                'depense_Tax' => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $depense = Depense::find($id);

            if (!$depense) {
                return response()->json([
                    'message' => 'Depense introuvable'
                ], 404);
            }

            $existingDepense = Depense::where('depense', $request->depense)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingDepense) {
                return response()->json([
                    'message' => 'depense ne peut pas être dupliqué'
                ], 400);
            }

            $depense->update([
                'depense' => $request->depense,
                'depense_Tax' => $request->depense_Tax,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Depense updated successfully',
                'id' => $depense->id
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
                        $depense = Depense::find($id);

                        if (!$depense) {
                            return response()->json([
                                'message' => 'Depense not found'
                            ], 404);
                        }

                        $depense->delete();

                        DB::commit();

                        return response()->json([
                            'message' => 'Depense deleted successfully'
                        ], 200);
                    } catch (Exception $e) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Something went wrong. Please try again later.'
                        ], 400);
                    }
                }


}
