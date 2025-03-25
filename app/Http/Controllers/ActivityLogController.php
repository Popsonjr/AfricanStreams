<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            $query = ActivityLog::where('user_id', $user->id)
                ->orderBy('activity_date', 'desc')
                ->orderBy('activity_time', 'desc');

            // Filter by date range if provided
            if ($request->filled('start_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Filter by activity type if provided
            if ($request->filled('activity_type')) {
                $activityType = $request->activity_type;
                $query->where('activity', 'LIKE', "%{$activityType}%");
            }

            $activityLogs = $query->paginate(100, ['*'], 'page', $request->query('page', 1));

            return response()->json([
                'page' => $activityLogs->currentPage(),
                'results' => $activityLogs->items(),
                'total_pages' => $activityLogs->lastPage(),
                'total_results' => $activityLogs->total(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error fetching activity logs'
            ], 500);
        }
    }

    /**
     * Get activity logs for a specific user (admin only)
     */
    public function show(Request $request, $userId)
    {
        try {
            // Check if user is admin
            if (!Auth::user()->is_admin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($userId);
            
            $query = ActivityLog::where('user_id', $userId)
                ->orderBy('activity_date', 'desc')
                ->orderBy('activity_time', 'desc');

            // Filter by date range if provided
            if ($request->filled('start_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Filter by activity type if provided
            if ($request->filled('activity_type')) {
                $activityType = $request->activity_type;
                $query->where('activity', 'LIKE', "%{$activityType}%");
            }

            $activityLogs = $query->paginate(20, ['*'], 'page', $request->query('page', 1));

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'page' => $activityLogs->currentPage(),
                'results' => $activityLogs->items(),
                'total_pages' => $activityLogs->lastPage(),
                'total_results' => $activityLogs->total(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error fetching activity logs'
            ], 500);
        }
    }

    /**
     * Get activity summary for the authenticated user
     */
    public function summary(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get total activities
            $totalActivities = ActivityLog::where('user_id', $user->id)->count();
            
            // Get activities by date (last 7 days)
            $recentActivities = ActivityLog::where('user_id', $user->id)
                ->where('activity_date', '>=', now()->subDays(7))
                ->count();
            
            // Get most common activities
            $commonActivities = ActivityLog::where('user_id', $user->id)
                ->selectRaw('activity, COUNT(*) as count')
                ->groupBy('activity')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'total_activities' => $totalActivities,
                'recent_activities' => $recentActivities,
                'common_activities' => $commonActivities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error fetching activity summary'
            ], 500);
        }
    }

    /**
     * Admin: Get activity logs for any user, latest first
     */
    public function userLogs(Request $request, $user_id)
    {
        try {
            // Check if user is admin
            // if (!Auth::user() || !Auth::user()->is_admin) {
            //     return response()->json(['error' => 'Unauthorized'], 403);
            // }

            $query = ActivityLog::where('user_id', $user_id)
                ->orderByDesc('activity_date')
                ->orderByDesc('activity_time');

            // Filter by date range if provided
            if ($request->filled('start_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            // Filter by activity type if provided
            if ($request->filled('activity_type')) {
                $activityType = $request->activity_type;
                $query->where('activity', 'LIKE', "%{$activityType}%");
            }

            $activityLogs = $query->paginate(100, ['*'], 'page', $request->query('page', 1));

            return response()->json([
                'user_id' => $user_id,
                'page' => $activityLogs->currentPage(),
                'results' => $activityLogs->items(),
                'total_pages' => $activityLogs->lastPage(),
                'total_results' => $activityLogs->total(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'Error fetching activity logs'
            ], 500);
        }
    }
}