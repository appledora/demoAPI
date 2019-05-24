<?php

namespace App\Http\Controllers;

use App\Notifications\SignupActivate;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class DetailsController extends Controller
{

    public $successStatus = 200;


    /**
     * Returns Authenticated User Details
     */
    public function details()
    {
        return response()->json(['user' => auth()->User()], 200);
    }



}