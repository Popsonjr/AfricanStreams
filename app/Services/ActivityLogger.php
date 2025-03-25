<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    /**
     * Log a user activity
     *
     * @param User|int $user User model or user ID
     * @param string $activity Human readable activity description
     * @param array|null $metadata Extra details (optional)
     * @return ActivityLog|null
     */
    public static function log($user, string $activity, array $metadata = null)
    {
        try {
            // Get user ID if user object is passed
            $userId = is_object($user) ? $user->id : $user;
            
            // Get current date and time
            $now = now();
            
            $activityLog = ActivityLog::create([
                'user_id' => $userId,
                'activity' => $activity,
                'metadata' => $metadata,
                'activity_date' => $now->toDateString(),
                'activity_time' => $now->toTimeString(),
            ]);

            Log::info('Activity logged', [
                'user_id' => $userId,
                'activity' => $activity,
                'activity_log_id' => $activityLog->id
            ]);

            return $activityLog;
        } catch (\Exception $e) {
            Log::error('Failed to log activity', [
                'user_id' => is_object($user) ? $user->id : $user,
                'activity' => $activity,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Log movie watching activity
     */
    public static function logMovieWatched($user, $movie)
    {
        return self::log($user, "Watched \"{$movie->title}\"", [
            'movie_id' => $movie->id,
            'movie_title' => $movie->title
        ]);
    }

    /**
     * Log movie rating activity
     */
    public static function logMovieRated($user, $movie, $rating)
    {
        return self::log($user, "Rated \"{$movie->title}\" {$rating}/10", [
            'movie_id' => $movie->id,
            'movie_title' => $movie->title,
            'rating' => $rating
        ]);
    }

    /**
     * Log watchlist addition activity
     */
    public static function logAddedToWatchlist($user, $movie)
    {
        return self::log($user, "Added \"{$movie->title}\" to watchlist", [
            'movie_id' => $movie->id,
            'movie_title' => $movie->title
        ]);
    }

    /**
     * Log subscription activity
     */
    public static function logSubscription($user, $package)
    {
        return self::log($user, "Subscribed to the {$package} package", [
            'package' => $package
        ]);
    }

    /**
     * Log movie creation activity (for admins)
     */
    public static function logMovieCreated($user, $movie)
    {
        return self::log($user, "Created movie \"{$movie->title}\"", [
            'movie_id' => $movie->id,
            'movie_title' => $movie->title
        ]);
    }

    /**
     * Log movie update activity (for admins)
     */
    public static function logMovieUpdated($user, $movie)
    {
        return self::log($user, "Updated movie \"{$movie->title}\"", [
            'movie_id' => $movie->id,
            'movie_title' => $movie->title
        ]);
    }

    /**
     * Log movie deletion activity (for admins)
     */
    public static function logMovieDeleted($user, $movieTitle)
    {
        return self::log($user, "Deleted movie \"{$movieTitle}\"", [
            'movie_title' => $movieTitle
        ]);
    }
} 