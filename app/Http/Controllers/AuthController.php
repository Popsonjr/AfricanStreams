<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
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
            'email' => 'required|email|unique:users',
            'password' => 'required|String|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        // return response()->json(['user' => $user, 'token' => $token], 201);
        return response()->json(compact('user','token'), 201);
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
            If (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    
            return response()->json(['token' =>$token]);
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
            Log::error('Error while refreshing token', [
               'message' => $e->getMessage(),
               'file' => $e->getFile(),
               'line' => $e->getLine()
            ]);
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
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Invalid Token'
            ], 400);
        }

        return response()->json(compact('user'));
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
        return Socialite::driver('google')->redirect();
    }
    
    /**
     * handleGoogleCallback
     *
     * @return void
     */
    public function handleGoogleCallback() {
        try {
        
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

        } catch (Exception $th) {
            return response()->json(['Error' => $th->getMessage()], 500);
        }
    }
    
    /**
     * verifyEmail
     *
     * @param  mixed $request
     * @return void
     */
    public function verifyEmail(Request $request) {
        $request->validate([
            'token' => 'required'
        ]);

        $user = User::where('verification_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        return response()->json(['message' => 'Email verified successfully']);
    }
    
    /**
     * resendVerificationEmail
     *
     * @param  mixed $request
     * @return void
     */
    public function resendVerificationEmail(Request $request) {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        
            if ($user->email_verified_at) {
                return response()->json(['message' => 'Email is already verified'], 400);
            }

            $token = Str::random(64);
            $user->update([
                'verification_token' => $token
            ]);

            Mail::to($user->email)->queue(new VerificationEmail($token));

            return response()->json([
                'message' => 'Verification email sent',
            ]);
        } catch (Exception $th) {
            return response()->json(['Error' => $th->getMessage()], 500);
        }
    }
}