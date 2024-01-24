<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;



class AuthController extends Controller
{
    public function signup(Request $request)
{
    // Validation logic here
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'dob' => 'required|date_format:d-m-Y',
    ]);
    // dd($request->dob);
    
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'date_of_birth' => Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d'),
    ]);

    $token = $user->createToken('MyApp')->accessToken;

    return response()->json(['token' => $token], 200);
}

}
