<?php

namespace App\Http\Controllers;

use App\Models\depense;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            if (!$request->filled(['depense', 'depense_Tax'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            if($request->depense_Tax <= 0) {
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
            if (!$request->filled(['depense', 'depense_Tax'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $depense = Depense::find($id);

            if (!$depense) {
                return response()->json([
                    'message' => 'Depense not found'
                ], 404);
            }

            $existingDepense = Depense::where('depense_Tax', $request->depense_Tax)
                ->where('id', '!=', $id)
                ->exists();

            if (!$existingDepense) {
                return response()->json([
                    'message' => 'Depense not Found'
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
