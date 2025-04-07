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
use Illuminate\Support\Facades\Storage;
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
            Log::info('frontend url', ['url' => env('APP_FRONTEND')]);
            // Mail::to($user->email)->queue(new VerificationEmail($token));
            Mail::to($user->email)->send(new VerificationEmail($token, env('APP_FRONTEND')));

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
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'User not found'
                ], 401);
                // return response()->json(['error' => 'User not found'], 401);
            }
            If (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid password',
                    'error' => 'Invalid password'
                ], 401);
                // return response()->json(['error' => 'Invalid password'], 401);
            }
            
            return response()->json([
                'token' => $token, 
                'user' => $user]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Could not create token'
            ], 500);
            // return response()->json(['error' => 'Could not create token'], 500);
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
                return response()->json(['error' => 'User not found'], 401);
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
            return response()->json([
                'message' => $th->getMessage(),
                'error' => 'Error while handlong google callback'
            ], 500);
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
                return response()->json([
                    'error' => 'Invalid token', 
                    'message' => 'Invalid token'
                ], 400);
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
                return response()->json([
                    'error' => 'User not found', 
                    'message' => 'User not found'], 401);
            }
        
            if ($user->email_verified_at) {
                return response()->json([
                    'error' => 'Email is already verified', 
                    'message' => 'Email is already verified'], 400);
            }

            $token = Str::random(64);
            $user->update([
                'verification_token' => $token
            ]);

            Mail::to($user->email)->send(new VerificationEmail($token, env('APP_FRONTEND')));

            return response()->json([
                'message' => 'Verification email sent',
            ]);
        } catch (Exception $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'error' => 'Error whike resending verification email'
            ], 500);
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
                'Hashed Token' => Hash::make($token),
            ]);
            Mail::to($request->email)->send(new ResetPasswordEmail($token, env('APP_FRONTEND')));

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

    public function delete(Request $request) {
        try {
            $request->validate([
                'id' => 'required|exists:users,id'
            ]);

            $userToDelete = User::findOrFail($request->id);
            $currentAdmin = JWTAuth::user();

            // Log the admin being deleted
            Log::info('Attempting to delete user:', [
                'user_id' => $userToDelete->id,
                'email' => $userToDelete->email,
                'current_admin_id' => $currentAdmin->id
            ]);

            // // Prevent self-deletion
            // if ($userToDelete->id === $currentAdmin->id) {
            //     return response()->json([
            //         'message' => 'You cannot delete your own account'
            //     ], 403);
            // }

            // Delete profile image if exists
            if ($userToDelete->profile_image) {
                Storage::disk('public')->delete($userToDelete->profile_image);
            }

            // Explicitly set deleted_at and save
            $userToDelete->deleted_at = now();
            $userToDelete->save();

            // Log after deletion
            Log::info('Admin soft deleted:', [
                'admin_id' => $userToDelete->id,
                'deleted_at' => $userToDelete->deleted_at
            ]);

            return response()->json([
                'message' => 'User account deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Delete Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while deleting user account'
            ], 500);
        }
    }

    public function batchDestroy(Request $request)
    {
        // Authorize admin (adjust as needed for your app)
        // if (!$request->user() || !$request->user()->is_admin) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $ids = $request->input('ids');
        $deleted = [];
        $failed = [];

        $users = User::whereIn('id', $ids)->get();
        $currentUser = JWTAuth::user();
        foreach ($users as $user) {
            try {
                // Prevent self-deletion
            if ($user->id === $currentUser->id) {
                $failed[] = $currentUser->id;
                continue;
                // return response()->json([
                //     'message' => 'You cannot delete your own account'
                // ], 403);
            }

            // Delete profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Explicitly set deleted_at and save
            $user->deleted_at = now();
            $user->save();

            // Log after deletion
            Log::info('User soft deleted:', [
                'admin_id' => $user->id,
                'deleted_at' => $user->deleted_at
            ]);

                // Delete the movie
                if($user->delete()) {
                    $deleted[] = $user->id;
                } else {
                    $failed[] = $user->id;
                }
                
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $user->id,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return response()->json([
            'message' => 'Batch users deleted successfully',
            'deleted' => $deleted,
            'failed' => $failed,
        ]);
    }

    public function getAllUsers() {
        try {
            // Get only active admins (explicitly exclude soft-deleted) with pagination
            $users = User::whereNull('deleted_at')->paginate(100);
            
            // Log the count of active admins
            Log::info('Active user count:', [
                'total' => $users->total()
            ]);

            return response()->json([
                'users' => $users->items(),
                'pagination' => [
                    'total' => $users->total(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Get All Users Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while fetching users'
            ], 500);
        }
    }
}