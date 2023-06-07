<?php

namespace App\Http\Controllers;

use App\Models\employeeRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeRoleController extends Controller
{
    public function index()
    {
        try {
            $employeeRoles = EmployeeRole::all();
            return response()->json($employeeRoles);
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
            if (!$request->filled(['role_name'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $existingRole = EmployeeRole::where('role_name', $request->role_name)->exists();

            if ($existingRole) {
                return response()->json([
                    'message' => 'Role already exists'
                ], 400);
            }

            $addedRole = EmployeeRole::create([
                'role_name' => $request->role_name,
            ]);

            if (!$addedRole) {
                return response()->json([
                    'message' => 'Role not created. Please try again.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Role added successfully',
                'id' => $addedRole->id,
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
            $role = EmployeeRole::find($id);

            if (!$role) {
                return response()->json([
                    'message' => 'Role not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Role found',
                'role' => $role
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
            if (!$request->filled(['role_name'])) {
                return response()->json([
                    'message' => 'Please fill all required fields.'
                ], 400);
            }

            $role = EmployeeRole::find($id);

            if (!$role) {
                return response()->json([
                    'message' => 'Role not found'
                ], 404);
            }

            $existingRole = EmployeeRole::where('role_name', $request->role_name)
                ->where('id', '!=', $id)
                ->exists();

            if ($existingRole) {
                return response()->json([
                    'message' => 'Role already exists'
                ], 400);
            }

            $role->update([
                'role_name' => $request->role_name,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Role updated successfully',
                'id' => $role->id
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
            $role = EmployeeRole::find($id);

            if (!$role) {
                return response()->json([
                    'message' => 'Role not found'
                ], 404);
            }

            $role->delete();

            DB::commit();

            return response()->json([
                'message' => 'Role deleted successfully'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 400);
        }
    }

}
