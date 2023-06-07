<?php

namespace App\Http\Controllers;

use App\Http\Resources\employeeResource;
use App\Models\employee;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            // $employees = Employee::all();
            // return response()->json($employees);


            $employees = Employee::all();

            return employeeResource::collection($employees);

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
            if (!$request->filled(['nom_employee', 'code_employee', 'CIN_employee', 'matricule_employee', 'telephone_employee','date_embauche', 'role_id'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $added = Employee::create([
                'nom_employee' => $request->nom_employee,
                'code_employee' => $request->code_employee,
                'CIN_employee' => $request->CIN_employee,
                'matricule_employee' => $request->matricule_employee,
                'telephone_employee' => $request->telephone_employee,
                'email_employee' => $request->email_employee,
                'adresse_employee' => $request->adresse_employee,
                'date_embauche' => $request->date_embauche,
                'role_id' => $request->role_id,
            ]);

            if (!$added) {
                return response()->json([
                    'message' => 'Employee not recorded. An message occurred.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Employee added successfully',
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
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found'
                ], 400);
            }

            return response()->json([
                'message' => 'Employee found',
                'employee' => $employee
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
            if (!$request->filled(['nom_employee', 'code_employee', 'CIN_employee', 'matricule_employee', 'telephone_employee', 'email_employee', 'adresse_employee', 'date_embauche', 'role_id'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found'
                ], 404);
            }

            $employee->update([
                'nom_employee' => $request->nom_employee,
                'code_employee' => $request->code_employee,
                'CIN_employee' => $request->CIN_employee,
                'matricule_employee' => $request->matricule_employee,
                'telephone_employee' => $request->telephone_employee,
                'email_employee' => $request->email_employee,
                'adresse_employee' => $request->adresse_employee,
                'date_embauche' => $request->date_embauche,
                'role_id' => $request->role_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Employee updated successfully',
                'id' => $employee->id
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
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found'
                ], 404);
            }

            $employee->delete();

            DB::commit();

            return response()->json([
                'message' => 'Employee deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

}
