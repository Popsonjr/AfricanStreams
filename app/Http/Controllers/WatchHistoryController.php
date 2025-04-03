<?php

namespace App\Http\Controllers;

use App\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WatchHistoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $history = WatchHistory::where('user_id', $request->user()->id)
                ->with(['movie.genres'])
                ->orderBy('watched_at', 'desc')
                ->paginate(20);

            return response()->json([
                'page' => $history->currentPage(),
                'results' => $history,
                'total_pages' => $history->lastPage(),
                'total_results' => $history->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch watch history', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch watch history'], 500);
        }
    }

    public function indexAll(Request $request)
    {
        try {
            $history = WatchHistory::with(['user', 'movie.genres'])
                ->orderBy('watched_at', 'desc')
                ->paginate(20);

            return response()->json([
                'page' => $history->currentPage(),
                'results' => $history,
                'total_pages' => $history->lastPage(),
                'total_results' => $history->total(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch all watch histories', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch watch histories'], 500);
        }
    }

    public function show(WatchHistory $watchHistory)
    {
        try {
            if ($watchHistory->user_id !== auth('api')->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($watchHistory->load(['movie.genres']));
        } catch (\Exception $e) {
            Log::error('Failed to fetch watch history entry', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch watch history entry'], 500);
        }
    }

    public function updateProgress(Request $request, WatchHistory $watchHistory)
    {
        $request->validate([
            'progress' => 'required|integer|min:0',
        ]);

        try {
            if ($watchHistory->user_id !== auth('api')->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $watchHistory->update([
                'progress' => $request->progress,
                'watched_at' => now(),
            ]);

            return response()->json($watchHistory);
        } catch (\Exception $e) {
            Log::error('Failed to update watch progress', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update watch progress'], 500);
        }
    }

    public function destroy(WatchHistory $watchHistory)
    {
        try {
            if ($watchHistory->user_id !== auth('api')->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $watchHistory->delete();
            return response()->json(['message' => 'Watch history entry deleted']);
        } catch (\Exception $e) {
            Log::error('Failed to delete watch history entry', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete watch history entry'], 500);
        }
    }
}