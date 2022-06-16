<?php

namespace App\Http\Controllers;

use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;



class UserController extends Controller
{
    //get all app user
    public function app_users()
    {
        $users = User::role(['user', 'parent'])->get();
        return response()->json([
            "users"=>$users,
        ], 200);
    }

    //get all app user
    public function system_users()
    {
        $users = User::whereHas('roles', function($q){
                $q->where([['name', '!=', 'user'], ['name', '!=', 'school'], ['name', '!=', 'teacher'], ['name', '!=', 'parent']]);
            })->with('roles')->get();

        return response()->json([
            "users"=>$users,
        ], 200);
    }

    /**Create user and assign role*/
    public function store(Request $request)
    {
        $validatedData =  Validator::make($request->all(),[
            'fname' => 'required',
            'lname' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|unique:users',
            'role_id' => 'required'
            // 'password' => 'required',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $user = new User();
        $user->username = $request->username;
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->password = bcrypt(123456);
        $user->save();

        $role = Role::where('id', $request->role_id)->first();
        //attach user role
        $user->assignRole($role->name);
        // $user->revokePermissionTo('edit articles');

        return response()->json([
            "success"=>true,
        ], 200);
    }


    public function update(Request $request, $id)
    {
        info($request->all());
        $validatedData =  Validator::make($request->all(),[
            'fname' => 'required',
            'lname' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            // 'role_id' => 'required'
            // 'password' => 'required',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $user = User::where('id', $id)->first();
        $user->update([
            "username" =>$request->username,
            "fname" =>$request->fname,
            "lname" =>$request->lname,
            "email" =>$request->email,
            "phone_number" =>$request->phone_number,
            // $user->password = bcrypt(123456);
        ]);


        if ($request->has('role_id')) {
            $role = Role::where('id', $request->role_id)->first();

            $user->roles()->detach();
            $user->assignRole($role->name);
            // $user->revokePermissionTo('edit articles');
            // $roles = $user->getRoleNames();
        }

        return response()->json([
            "success"=>true,
            "user" => $user
        ], 200);
    }

    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        $user->roles()->detach();
        $user = User::destroy($id);

        return response()->json([
            "success"=>true,
        ], 200);
    }

    public function block_user($id){
        $user = User::where('id', $id)->first();
        if ($user->id == Auth::user()->id){
            return redirect()->back()->with('warning', 'Request declined, you cannot block yourself from using the system.');
        }
        if ($user->status == false){
            return redirect()->back()->with('warning', 'User is already blocked.');
        }
        if ($user){
            $user->update([
                'status'=>false,
                'updated_at'=>Carbon::now()
            ]);
            return redirect()->back()->with('success', $user->fname.' has successfully been blocked from accessing the Taji web portal');
        }
        else{
            return redirect()->back()->with('danger', 'Request declined, user not found.');
        }
    }

    public function unblock_user($id){
        $user = User::find(decrypt($id));
        if ($user->id == Auth::user()->id){
            return redirect()->back()->with('warning', 'Request declined, you cannot unblock yourself from using the system.');
        }
        if ($user->status == true){
            return redirect()->back()->with('warning', 'User is not blocked.');
        }
        if ($user){
            $user->update([
                'status'=>true,
                'updated_at'=>Carbon::now()
            ]);
            return redirect()->back()->with('success', $user->fname.' has successfully been blocked from accessing the Taji web portal');
        }
        else{
            return redirect()->back()->with('danger', 'Request declined, user not found.');
        }
    }
    public function total_users() {
        $user = User::get()->count();
        return response()->json($user - 2);
    }
}
