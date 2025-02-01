<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|String|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|String|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        return response()->json(['user' => $user, 'token' => $token], 201);
    }
    
    public function login(Request $request) {
        $credentials = $request->only(
            'email',
            'password'
        );
        
        If (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' =>$token]);
    }

    public function logout() {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh() {
        return response()->json(['token' => JWTAuth::refresh()]);
    }

    public function me() {
        return response()->json(JWTAuth::user());
    }

    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {
        $googleUser = Socialite::driver('google')->user();

        $user = User::firstOrCreate(
            [
                'email' => $googleUser->getEmail(),
            ], 
            [
                'name' => $googleUser->getName(),
                'password' => bcrypt(str()->random(16)),
            ]
            );

            $token = JWTAuth::fromUser($user);
            return response()->json(['user' => $user, 'token' => $token]);
    }
}