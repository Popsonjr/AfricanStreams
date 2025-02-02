<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
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
}