<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index()
    {
        try {
            $companies = Company::all();
            return response()->json($companies);
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
                'name' => 'required',
                'ICE' => 'required',
                'IF' => 'required',
                'RC' => 'required',
                'adresse' => 'required',
                'email' => 'email',
                'telephone' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }


            $existingCompany = Company::first();

            if ($existingCompany) {
                return response()->json([
                    'message' => 'A company record already exists'
                ], 400);
            }


            $founded = Company::where('name', $request->name)->exists();

            if ($founded) {
                return response()->json([
                    'message' => 'Company already exists'
                ], 400);
            }

            $company = Company::create([
                'name' => $request->name,
                'ICE' => $request->ICE,
                'IF' => $request->IF,
                'RC' => $request->RC,
                'adresse' => $request->adresse,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'fax' => $request->fax,
                'capital'=> $request->capital,
            ]);

            if (!$company) {
                return response()->json([
                    'message' => 'Company could not be added. Please try again later.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Company added successfully',
                'id' => $company->id,
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
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'message' => 'Company not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Company found',
                'company' => $company
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
                'name' => 'required',
                'ICE' => 'required',
                'IF' => 'required',
                'RC' => 'required',
                'adresse' => 'required',
                'email' => 'email',
                'telephone' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'message'=> 'Company not found'
                ], 404);
            }  $company->update([
                'name' => $request->name,
                'ICE' => $request->ICE,
                'IF' => $request->IF,
                'RC' => $request->RC,
                'adresse' => $request->adresse,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'fax' => $request->fax,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Company updated successfully',
                'id' => $company->id
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
                $company = Company::find($id);

                if (!$company) {
                    return response()->json([
                        'message' => 'Company not found'
                    ], 404);
                }

                $company->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Company deleted successfully'
                ], 200);
            } catch (Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                ], 400);
            }
        }
}
