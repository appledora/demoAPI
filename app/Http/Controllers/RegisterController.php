<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegisterController extends Controller
{
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
        $user->save(); // creates a validated user in the database
        return $user;
    }

}