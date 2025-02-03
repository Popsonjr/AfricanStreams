<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;

class AdminAuthController extends Controller
{
     /**
     * register
     *
     * @param  mixed $request
     * @return void
     */
    public function register(Request $request) {
        try {
            $request->validate([
                'name' => 'required|String|max:255',
                'email' => 'required|email|unique:admins',
                'password' => 'required|String|min:6',
            ]);

            $admin = Admin::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = JWTAuth::fromUser($admin);
            // return response()->json(['user' => $user, 'token' => $token], 201);
            return response()->json(compact('admin','token'), 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while registering admin user'
            ], 500);
        }
    }

    /**
     * login
     *
     * @param  mixed $request
     * @return void
     */
    public function login(Request $request) {
        $credentials = $request->only(
            'email',    
            'password'
        );

        try {
            $admin = Admin::where('email', $credentials['email'])->first();
            if(!$admin) {
                return response()->json(['error' => 'Admin user not found'], 404);
            }
            // If (!$token = JWTAuth::attempt($credentials)) {
                If (!$token = auth('admin')->attempt($credentials)) {
                return response()->json(['error' => 'Invalid password'], 401);
            }
            
            return response()->json([
                'token' => $token, 
                'admin' => $admin]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }

    /**
     * logout
     *
     * @return void
     */
    public function logout() {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch(JWTException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Could not logout',
            ], 500);
        }
    }
    
    /**
     * refresh
     *
     * @return void
     */
    public function refresh() {
        try {
            $token = JWTAuth::getToken();
            return response()->json(['token' => JWTAuth::refresh($token)]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to refresh token, please try again',
                'message' => $e->getMessage()
            ], 500);
        }
        
    }

    /**
     * Get Authenticated User
     *
     * @return User
     */
    public function getUser() {
        try {
            if (! $admin = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'Admin user not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Invalid Token'
            ], 400);
        }

        return response()->json(compact('admin'));
    }

    public function me() {
        return response()->json(JWTAuth::user());
    }

    /**
     * redirectToGoogle
     *
     * @return void
     */
    public function redirectToGoogle() {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while redirecting to Google'
            ], 500);
        }
    }
    
    /**
     * handleGoogleCallback
     *
     * @return void
     */
    public function handleGoogleCallback() {
        try {
        
            $googleUser = Socialite::driver('google')->user();

            $admin = Admin::firstOrCreate(
                [
                    'email' => $googleUser->getEmail(),
                ], 
                [
                    'name' => $googleUser->getName(),
                    'password' => bcrypt(str()->random(16)),
                ]
            );

            $token = JWTAuth::fromUser($admin);
            return response()->json(['user' => $admin, 'token' => $token]);

        } catch (Exception $th) {
            return response()->json(['Error' => $th->getMessage()], 500);
        }
    }
}