<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Mail\VerificationEmail;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
// use Tymon\JWTAuth\Contracts\Providers\Auth;
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
        try {
            $request->validate([
                'first_name' => 'required|String|max:255',
                'last_name' => 'required|String|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|String|min:6',
            ]);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = Str::random(64);
            $user->update([
                'verification_token' => $token
            ]);

            // Mail::to($user->email)->queue(new VerificationEmail($token));
            Mail::to($user->email)->send(new VerificationEmail($token));

            return response()->json(compact('user'), 201);

            // $token = JWTAuth::fromUser($user);
            // return response()->json(['user' => $user, 'token' => $token], 201);
            // return response()->json(compact('user','token'), 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while registering user'
            ], 403);
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
            $user = User::where('email', $credentials['email'])->first();
            if(!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            If (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid password'], 401);
            }
            
            return response()->json([
                'token' => $token, 
                'user' => $user]);
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
            // Log::error('Error while refreshing token', [
            //    'message' => $e->getMessage(),
            //    'file' => $e->getFile(),
            //    'line' => $e->getLine()
            // ]);
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
        try {
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
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while verifying email'
            ], 500);
        }
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

            Mail::to($user->email)->send(new VerificationEmail($token));

            return response()->json([
                'message' => 'Verification email sent',
            ]);
        } catch (Exception $th) {
            return response()->json(['Error' => $th->getMessage()], 500);
        }
    }

    public function sendResetLink(Request $request) {
        try {
            $request->validate(['email' => 'required|email|exists:users,email']);

            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    // 'token' => $token,
                    'created_at' => now()
                ]
            );

            Log::info('send reset link ', [
                'Token' => $token,
                'Hashed Token' => Hash::make($token)
            ]);
            Mail::to($request->email)->queue(new ResetPasswordEmail($token));

            return response()->json(['message' => 'Reset link has been sent to your email.']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while resetting link'
            ], 500);
        }
    }

    public function resetPassword(Request $request) {
        try {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|min:6|confirmed',
            ]);

            $record = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

            if (!$record) {
                return response()->json(['message' => 'Invalid email or token'], 400);
            }
            
            if (!Hash::check($request->token, $record->token)) {
                return response()->json(['message' => 'Invalid token'], 400);
            }

            User::where('email', $request->email)->update([
                'password' => Hash::make($request->password)
            ]);

            DB::table('password_resets')->where('email', $request->email)->delete();

            return response()->json(['message' => 'Password reset successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while resetting password'
            ], 500);
        }
    }

    public function changePassword(Request $request) {
        try {
            $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed', 
            ]);

            $user = JWTAuth::user();

            if(!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Incorrect current password', 400]);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while changing password'
            ], 500);
        }
    }
}