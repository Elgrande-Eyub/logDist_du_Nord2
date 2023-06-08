<?php

namespace App\Http\Controllers;

use App\Models\client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index()
    {
        try {
            $clients = Client::all();
            return response()->json($clients);

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
                'nom_Client' => 'required',
                'code_Client' => 'required|unique:clients',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $added = Client::create($request->all());

            if (!$added) {
                return response()->json([
                    'message' => 'Client not recorded due to an error'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Client added successfully',
                'id' => $added->id,
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
            $client = Client::withTrashed()->find($id);

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Client found',
                'client' => $client
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
                'nom_Client' => 'required',
  /*               'code_Client' => 'required|unique:clients',
                'CIN_Client' => 'required',
                'ICE_Client' => 'required',
                'RC_Client' => 'required',
                'Pattent_Client' => 'required', */
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }


            $client = Client::find($id);

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found'
                ], 404);
            }

            $client->update($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Client updated successfully',
                'id' => $client->id
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
            $client = Client::find($id);

            if (!$client) {
                return response()->json([
                    'message' => 'Client not found'
                ], 404);
            }

            $client->delete();

            DB::commit();

            return response()->json([
                'message' => 'Client deleted successfully'
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

}
