<?php

namespace App\Http\Controllers;

use App\Notifications\SignupActivate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class PassportController extends Controller
{

    public $successStatus = 200;


    /**
     * Handles Registration Request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [ //basically checks that these fields are present
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'activation_token' => Str::random(60)
        ]);
        $user->save(); // saves the created new user
        $user->notify(new SignupActivate($user));

        $output['token'] = $user->createToken('nyanradio')->accessToken;
        $output['name'] = $user->name;
        return response()->json(['output' => $output], $this->successStatus);
    }

    public function signupActivate($token) //changes the userstatus  after mail verification
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'message' => __('auth.token_invalid or account already activated!')
            ], 404);
        }
        $user->active = true;
        $user->activation_token = '';
        $user->save();
        return $user;
    }

    /**
     * Handles Login Request
     */
    public function login(Request $request)
    {
        $request->validate([ //checks whether these fields are present
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = [   //creates an object
            'email' => $request->email,
            'password' => $request->password,
            'active' => 1, //verified
            'deleted_at' => null
        ];

        if (auth()->attempt($credentials)) {

            $output['token'] = auth()->user()->createToken('nyanchat')->accessToken;
            $output['email'] = $credentials->email;
            return response()->json(['output' => $output], 200); //will show the token and user's email
        } else {
            return response()->json(['error' => 'UnAuthorised'], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     */
    public function details()
    {
        return response()->json(['user' => auth()->User()], 200);
    }

    /**
     * Logout user (Revoke the token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}