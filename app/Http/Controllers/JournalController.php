<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JournalController extends Controller
{
    public function index()
    {
        try {

            $Journals =  Journal::all();
            return response()->json($Journals);


        } catch(Exception $e) {

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
                'Code_journal' => 'required',
                'type' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }



            $founded = Journal::where('Code_journal', $request->Code_journal)->exists();

            if($founded) {
                return response()->json([
                    'message' => 'Journal is Already Exists'
                ], 400);
            }


            $added = Journal::create([

             'Code_journal'=>$request->Code_journal,
             'type'=>$request->type,
            ]);


            if(!$added) {

                return response()->json([
                    'message' => 'Journal not recorded based on message'
                ], 400);
            }


            DB::commit();

            return response()->json([
             'message' => 'Journal Added Successfully',
             'id'=>$added->id,
        ], 200);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }


    public function show($id)
    {

        try {



            $founded = Journal::find($id);

            if(!$founded) {
                return response()->json([
                    'message' => 'Journal not found'
                ], 400);
            }

            return response()->json([
                'message' => 'Journal Found',
                'Journal requested' => $founded

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
                'Code_journal' => 'required',
                'type' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }



            $journal = Journal::find($id);

            if (!$journal) {
                return response()->json([
                    'message' => 'Journal not found'
                ], 404);
            }

            $existingJournal = Journal::where('Code_journal', $request->Code_journal)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingJournal) {
                return response()->json([
                    'message' => 'Journal already exists'
                ], 400);
            }

            $journal->update([
                'Code_journal' => $request->Code_journal,
                'type' => $request->type,

            ]);




            DB::commit();

            return response()->json([
                'message' => 'Journal updated successfully',
                'id' => $journal->id
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
        $journal = Journal::find($id);

        if (!$journal) {
            return response()->json([
                'message' => 'Journal not found'
            ], 404);
        }

        $journal->delete();

        DB::commit();

        return response()->json([
            'message' => 'Journal deleted successfully'
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Something went wrong. Please try again later.'
        ], 400);
    }
}

}
