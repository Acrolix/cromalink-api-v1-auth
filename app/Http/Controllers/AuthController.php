<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function verify()
    {
        if (! Auth::check()) {
            return response()->json([
                'status' => 'invalid',
                'user' => null
            ], 401);
        }
        return response()->json([
            'status' => 'valid',
            'user' => Auth::user()->id
        ], 200);
    }
}
