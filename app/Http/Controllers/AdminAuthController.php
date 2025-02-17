<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
     /**
     * register
     *
     * @param  mixed $request
     * @return void
     */
    public function register(Request $request) {
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
        return response()->json(compact('admin','token'), 201);
    }
}