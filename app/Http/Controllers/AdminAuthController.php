<?php

namespace App\Http\Controllers;

use App\Traits\HelperTrait;
use App\Mail\ResetPasswordEmail;
use App\Mail\VerificationEmail;
use App\Models\Admin;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminAuthController extends Controller
{
    use HelperTrait;

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
                'email' => 'required|email|unique:admins',
                'password' => 'required|String|min:6',
                'phone_number' => [
                    'nullable',
                    'string',
                    'regex:/^(\+234|0)[0-9]{10}$/',  // Nigerian format
                    'regex:/^\+?[1-9]\d{1,14}$/',    // International format
                ],
                'profile_image' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg',
                    'max:8182' // 8MB max
                ]
            ]);

            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
            ];

            // Handle profile image upload if present
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $fileName = time() . '_' . $profileImage->getClientOriginalName();
                
                // Store in storage/app/public/admin-profiles
                $path = $profileImage->storeAs('admin-profiles', $fileName, 'public');
                $data['profile_image'] = $path;
            }

            $admin = Admin::create($data);
            return response()->json(compact('admin'), 201);

            // $token = JWTAuth::fromUser($admin);
            // return response()->json(['user' => $user, 'token' => $token], 201);
            // return response()->json(compact('admin','token'), 201);
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
                return response()->json([
                    'message' => 'User not found',
                    'error' => 'User not found'
                ], 401);
            }
            // If (!$token = JWTAuth::attempt($credentials)) {
                If (!$token = auth('admin')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid password',
                    'error' => 'Invalid password'
                ], 401);
            }
            
            return response()->json([
                'token' => $token, 
                'admin' => $admin]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Could not create token'
            ], 500);
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
                return response()->json(['error' => 'Admin user not found'], 401);
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

            $admin = Admin::where('verification_token', $request->token)->first();

            if (!$admin) {
                return response()->json(['message' => 'Invalid token'], 400);
            }

            $admin->update([
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

            $admin = Admin::where('email', $request->email)->first();

            if (!$admin) {
                return response()->json(['message' => 'Admin user not found'], 401);
            }
        
            if ($admin->email_verified_at) {
                return response()->json(['message' => 'Email is already verified'], 400);
            }

            $token = Str::random(64);
            $admin->update([
                'verification_token' => $token
            ]);

            Mail::to($admin->email)->send(new VerificationEmail($token, env('APP_FRONTEND')));

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
            $request->validate(['email' => 'required|email|exists:admins,email']);

            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    // 'token' => $token,
                    'created_at' => now()
                ]
            );
            Mail::to($request->email)->send(new ResetPasswordEmail($token, env('APP_FRONTEND')));

            return response()->json(['message' => 'Reset link sent']);
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
                'email' => 'required|email|exists:admins,email',
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

            Admin::where('email', $request->email)->update([
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

            $admin = JWTAuth::user();

            if(!Hash::check($request->current_password, $admin->password)) {
                return response()->json(['message' => 'Incorrect current password', 400]);
            }

            $admin->update([
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

    public function update(Request $request) {
        try {
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone_number' => [
                    'nullable',
                    'string',
                    'regex:/^(0\d{10}|\+234\d{10}|\+?[1-9]\d{1,14})$/'
                ],
                'profile_image' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,png,jpg',
                    'max:8182' // 8MB max
                ]
            ]);

            $admin = JWTAuth::user();
            if (!$admin) {
                return response()->json(['error' => 'Admin user not found'], 401);
            }

            // Get all validated data
            $data = $request->only([
                'first_name',
                'last_name',
                'phone_number'
            ]);

            // Remove null values but keep empty strings
            $data = array_filter($data, function($value) {
                return $value !== null;
            });


            // Handle profile image upload if present
            if ($request->hasFile('profile_image')) {
                Log::info("inside file block");
                // Delete old profile image if exists
                if ($admin->profile_image) {
                    Storage::disk('public')->delete($admin->profile_image);
                }

                $profileImage = $request->file('profile_image');
                $fileName = time() . '_' . $profileImage->getClientOriginalName();
                
                // Store in storage/app/public/admin-profiles
                $path = $profileImage->storeAs('admin-profiles', $fileName, 'public');
                $data['profile_image'] = $path;
                Log::info("path image", ['path' => $path]);
            }

            if (empty($data)) {
                return response()->json([
                    'message' => 'No data provided for update',
                    'received_data' => $request->all(),
                    'debug_info' => [
                        'content_type' => $request->header('Content-Type'),
                        'has_files' => $request->hasFile('profile_image'),
                        'all_input' => $request->input(),
                        'all_files' => $request->allFiles()
                    ]
                ], 400);
            }

            // Update the admin
            $updated = $admin->update($data);

            Log::info('Update Result:', ['success' => $data]);

            // Refresh the model to get updated data
            $admin->refresh();

            return response()->json([
                'message' => 'Profile updated successfully',
                'admin' => $admin
            ]);

        } catch (Exception $e) {
            Log::error('Update Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while updating admin profile'
            ], 500);
        }
    }

    /**
     * Delete admin user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request) {
        try {
            $request->validate([
                'admin_id' => 'required|exists:admins,id'
            ]);

            $adminToDelete = Admin::findOrFail($request->admin_id);
            $currentAdmin = JWTAuth::user();

            // Log the admin being deleted
            Log::info('Attempting to delete admin:', [
                'admin_id' => $adminToDelete->id,
                'email' => $adminToDelete->email,
                'current_admin_id' => $currentAdmin->id
            ]);

            // Prevent self-deletion
            if ($adminToDelete->id === $currentAdmin->id) {
                return response()->json([
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            // Delete profile image if exists
            if ($adminToDelete->profile_image) {
                Storage::disk('public')->delete($adminToDelete->profile_image);
            }

            // Explicitly set deleted_at and save
            $adminToDelete->deleted_at = now();
            $adminToDelete->save();

            // Log after deletion
            Log::info('Admin soft deleted:', [
                'admin_id' => $adminToDelete->id,
                'deleted_at' => $adminToDelete->deleted_at
            ]);

            return response()->json([
                'message' => 'Admin account deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Delete Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while deleting admin account'
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
            'ids.*' => 'integer|exists:admins,id',
        ]);

        $ids = $request->input('ids');
        $deleted = [];
        $failed = [];

        $admins = Admin::whereIn('id', $ids)->get();
        $currentAdmin = JWTAuth::user();
        foreach ($admins as $admin) {
            try {
                // Prevent self-deletion
            if ($admin->id === $currentAdmin->id) {
                $failed[] = $currentAdmin->id;
                continue;
                // return response()->json([
                //     'message' => 'You cannot delete your own account'
                // ], 403);
            }

            // Delete profile image if exists
            if ($admin->profile_image) {
                Storage::disk('public')->delete($admin->profile_image);
            }

            // Explicitly set deleted_at and save
            $admin->deleted_at = now();
            $admin->save();

            // Log after deletion
            Log::info('Admin soft deleted:', [
                'admin_id' => $admin->id,
                'deleted_at' => $admin->deleted_at
            ]);

                // Delete the movie
                if($admin->delete()) {
                    $deleted[] = $admin->id;
                } else {
                    $failed[] = $admin->id;
                }
                
            } catch (\Exception $e) {
                $failed[] = [
                    'id' => $admin->id,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return response()->json([
            'message' => 'Batch admins deleted successfully',
            'deleted' => $deleted,
            'failed' => $failed,
        ]);
    }

    /**
     * Get all active admin users with pagination
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllAdmins() {
        try {
            // Get only active admins (explicitly exclude soft-deleted) with pagination
            $admins = Admin::whereNull('deleted_at')->paginate(100);
            
            // Log the count of active admins
            Log::info('Active admin count:', [
                'total' => $admins->total()
            ]);

            return response()->json([
                'admins' => $admins->items(),
                'pagination' => [
                    'total' => $admins->total(),
                    'per_page' => $admins->perPage(),
                    'current_page' => $admins->currentPage(),
                    'last_page' => $admins->lastPage(),
                    'from' => $admins->firstItem(),
                    'to' => $admins->lastItem()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Get All Admins Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error while fetching admin users'
            ], 500);
        }
    }
}