<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * @group Web - web routes
 *
 * Routes for Web
 */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::get();
        return response()->json([
            "roles"=>$roles,
        ], 200);
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:roles',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $role = Role::create(['name' => $request->name]);

        return response()->json([
            "success"=>true,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:roles',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $role = Role::where('id', $id)->first();
        $role->update([
            "name" =>$request->name
        ]);

        return response()->json([
            "success"=>true,
        ], 200);
    }

    public function destroy($id)
    {
        Role::destroy($id);

        return response()->json([
            "success"=>true,
        ], 200);
    }
}
