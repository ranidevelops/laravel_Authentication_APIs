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

    public function login(Request $request)
{
    // Validation logic 
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $credentials = request(['email', 'password']);

    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized user'], 401);
    }

    $user = $request->user();
    $token = $user->createToken('MyApp')->accessToken;

    return response()->json(['token' => $token], 200);
}

    public function getProfile()
    {
    $user = auth()->user();
    return response()->json(['user' => $user], 200);
    }

    public function updateProfile(Request $request)
{
    // Validation logic here
    $validator = Validator::make($request->all(), [
        'name' => 'string|max:255',
        'email' => 'email|unique:users,email,' . auth()->user()->id,
        'password' => 'string|min:8',
        'date_of_birth' => 'date_format:d-m-Y',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Update profile logic
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    if ($request->filled('email')) {
        // Check if the new email already exists for another user
        $existingUser = User::where('email', $request->email)
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return response()->json(['error' => 'The email has already been taken.'], 422);
        }

        $user->email = $request->email;
    }

    $user->fill($request->all());

    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    if ($request->filled('date_of_birth')) {
        $user->date_of_birth = Carbon::createFromFormat('d-m-Y', $request->date_of_birth)->format('Y-m-d');
    }

    $user->save();

    // Return the updated user profile
    return response()->json(['message' => 'Profile updated successfully', 'user' => $user], 200);
}

}
