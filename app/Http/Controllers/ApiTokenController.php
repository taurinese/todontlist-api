<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiTokenController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'password' => 'required'
        ]);

        $exists = User::where('email', $request->email)->exists();

        if($exists){
            return response()->json(['error' => 'You are already registered. Please login instead.']);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name
        ]);
        $token = $user->createToken($request->email)->plainTextToken;
        return response()->json([
            'token' => $token,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at
            ]);
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->where('tokenable_id', $user->id)->delete();

        $token = $user->createToken($request->email)->plainTextToken;

        return response()->json([
            'token' => $token,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at
            ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response(null, 204);
    }

    public function me(Request $request)
    {
        return response()->json([
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'created_at' => $request->user()->created_at
        ]);
    }


}
