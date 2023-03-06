<?php

namespace App\Http\Controllers;

use App\{User,Mzazi, School};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Jobs\SendResetPasswordMail;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'school_login', 'register', 'parent_register','parent_login', 'school_register', 'forgotPassword', 'resetPassword']]);
        // $this->middleware('auth:api', ['except' => ['login','register','uniqueEmail','forgotPassword','resetPassword','verifyOtp']]);
    }

    /**Register and get JWT token */
    public function register(Request $request)
    {
        $validatedData =  Validator::make($request->all(),[
            'fname' => 'required',
            'lname' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|unique:users',
            'password' => 'required',
        ], [
            'fname.required' => 'The first name is required',
            'lname.required' => 'The first name is required',
            'username.required' => 'The first name is required',
            'email.required' => 'The first name is required',
            'phone_number.required' => 'The first name is required',
            'password.required' => 'The first name is required',
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
        $user->password = bcrypt($request->password);

        if ($request->has('school_code')) {
            if ($request->school_code != '' || $request->school_code != NULL) {
                $school = School::where('school_register_id', $request->school_code)->first();
                if ($school) {
                    $user->school_id = $school->id;
                }
            }
        }

        $user->save();

        //attach user role
        $user->assignRole('user');

        $credentials = $request->only(['email', 'password']);
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'These credentials do not match with our records'], 401);

    }

    public function parent_register(Request $request) {
       $validatedData =  Validator::make($request->all(), [
            'fname' => 'required',
            'lname' => 'required',
            'username' => 'required',
            'email' => 'bail|required|email|unique:users',
            'phone_number' => 'required|unique:users',
            'password' => 'required',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 422);
        }

        $par = new User;
        $par->username = $request->username;
        $par->fname = $request->fname;
        $par->lname = $request->lname;
        $par->email = $request->email;
        $par->phone_number = $request->phone_number;
        $par->password = bcrypt($request->password);
        $par->save();

        $par->assignRole('parent');
        $credentials = $request->only(['email', 'password']);
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'Oops, something went wrong kindly try again later.'], 401);
    }

    public function school_register(Request $request) {
        $validatedData =  Validator::make($request->all(),[
            'fname' => 'required',
            'lname' => 'required',
            'username' => 'required',
            'email' => 'bail|required|email|unique:users',
            'phone_number' => 'required|unique:users',
            'password' => 'required',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message' => "invalid data",
                'errors' =>[$validatedData->messages()]
            ], 400);
        }

        $school = new User;
        $school->username = $request->username;
        $school->fname = $request->fname;
        $school->lname = $request->lname;
        $school->email = $request->email;
        $school->phone_number = $request->phone_number;
        $school->password = bcrypt($request->password);
        $school->save();

        $school->assignRole('school');
        $credentials = $request->only(['email', 'password']);
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'Oops, something went wrong kindly try again later.'], 401);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if($token = $this->guard()->attempt($credentials)) {
            if(Auth::user()->getRoleNames()[0] == 'admin' || Auth::user()->getRoleNames()[0] == 'data_entry') {
                return $this->respondWithToken($token);
            } else {
                return response()->json(['Oops, you have no privilege to access this site'], 401);
            }
        }

        return response()->json(['error' => 'These credentials do not match with our records'], 401);
    }

    public function school_login(Request $request) {
        $credentials = $request->only('email', 'password');
        if ($token = $this->guard()->attempt($credentials)) {
            $user = Auth::user();
            $school = School::where('id', $user->school_id)->first();
            if($school->suspend == 1) {
                return response()->json(['Oops, school is suspended. Try again later'], 401);
            }
            return $this->respondWithToken($token);
        }
        return response()->json(['error' => 'These credentials do not match with our records'], 401);
    }

    public function parent_login(Request $request) {

        $credentials = $request->only('email', 'password');

        if($token = $this->guard()->attempt($credentials)) {
            if(Auth::user()->getRoleNames()[0] == 'parent') {
                return $this->respondWithToken($token);
            } else {
                return response()->json(['Oops, you have no privilege to access this site'], 401);
            }
        }
        return response()->json(['error' => 'These credentials do not match with our records'], 401);
    }


    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $this->guard()->user(),
            'role' => $this->guard()->user()->getRoleNames()[0],
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
    // public function guard() {
    //     return \Auth::guard('api');
    // }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email'], 422);
        }

        $code = mt_rand(10000, 99999);

        $codes = User::all()->pluck('reset_password_code')->toArray();

        while (in_array($code, $codes)) {
            $code = mt_rand(10000, 99999);
        }

        $user->update([
            'reset_password_code' => $code,
        ]);

        SendResetPasswordMail::dispatchAfterResponse($user->email, $code);

        return response()->json(['message' => 'Password Reset Mail sent.']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }

        $user = User::where('reset_password_code', $request->code)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid code'], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password reset successfully'], 200);
    }
}
