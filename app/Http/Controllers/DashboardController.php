<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Movie;
use App\Models\TvShow;
use App\Models\WatchHistory;
use App\Models\Rating;
use App\Models\Subscription;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * General dashboard statistics
     */
    public function statistics()
    {
        $now = now();
        $startOfThisMonth = $now->copy()->startOfMonth();
        $endOfThisMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Users
        $totalUsers = User::count();
        $currentMonthUsers = User::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $previousMonthUsers = User::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $percentageTotalUsers = $previousMonthUsers > 0 ? (($currentMonthUsers - $previousMonthUsers) / $previousMonthUsers) * 100 : ($currentMonthUsers > 0 ? 100 : 0);

        // Subscribers
        $totalSubscribers = User::whereHas('subscriptions', function($q) {
            $q->where('status', 'active');
        })->count();
        $currentMonthSubscribers = User::whereHas('subscriptions', function($q) use ($startOfThisMonth, $endOfThisMonth) {
            $q->where('status', 'active')->whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth]);
        })->count();
        $previousMonthSubscribers = User::whereHas('subscriptions', function($q) use ($startOfLastMonth, $endOfLastMonth) {
            $q->where('status', 'active')->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
        })->count();
        $percentageTotalSubscribers = $previousMonthSubscribers > 0 ? (($currentMonthSubscribers - $previousMonthSubscribers) / $previousMonthSubscribers) * 100 : ($currentMonthSubscribers > 0 ? 100 : 0);

        // Movies
        $totalMovies = Movie::count();
        $currentMonthMovies = Movie::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $previousMonthMovies = Movie::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $percentageTotalMovies = $previousMonthMovies > 0 ? (($currentMonthMovies - $previousMonthMovies) / $previousMonthMovies) * 100 : ($currentMonthMovies > 0 ? 100 : 0);

        // Series
        $totalSeries = TvShow::count();
        $currentMonthSeries = TvShow::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $previousMonthSeries = TvShow::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $percentageTotalSeries = $previousMonthSeries > 0 ? (($currentMonthSeries - $previousMonthSeries) / $previousMonthSeries) * 100 : ($currentMonthSeries > 0 ? 100 : 0);

        // Views
        $totalViews = WatchHistory::count();
        $currentMonthViews = WatchHistory::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $previousMonthViews = WatchHistory::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $percentageTotalViews = $previousMonthViews > 0 ? (($currentMonthViews - $previousMonthViews) / $previousMonthViews) * 100 : ($currentMonthViews > 0 ? 100 : 0);

        // Likes
        $totalLikes = Rating::count();
        $currentMonthLikes = Rating::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->count();
        $previousMonthLikes = Rating::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $percentageTotalLikes = $previousMonthLikes > 0 ? (($currentMonthLikes - $previousMonthLikes) / $previousMonthLikes) * 100 : ($currentMonthLikes > 0 ? 100 : 0);

        // Average view time (using movie runtime as proxy)
        $averageViewTime = Movie::avg('runtime');
        $currentMonthAvgViewTime = Movie::whereBetween('created_at', [$startOfThisMonth, $endOfThisMonth])->avg('runtime');
        $previousMonthAvgViewTime = Movie::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->avg('runtime');
        $percentageAverageViewTime = $previousMonthAvgViewTime > 0 ? (($currentMonthAvgViewTime - $previousMonthAvgViewTime) / $previousMonthAvgViewTime) * 100 : ($currentMonthAvgViewTime > 0 ? 100 : 0);

        // New content
        $newMovies = Movie::where('created_at', '>=', now()->subDays(30))->count();
        $newSeries = TvShow::where('created_at', '>=', now()->subDays(30))->count();
        $newContent = $newMovies + $newSeries;
        $previousNewMovies = Movie::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $previousNewSeries = TvShow::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $previousNewContent = $previousNewMovies + $previousNewSeries;
        $percentageNewContent = $previousNewContent > 0 ? (($newContent - $previousNewContent) / $previousNewContent) * 100 : ($newContent > 0 ? 100 : 0);

        return response()->json([
            'total_users' => $totalUsers,
            'percentage_total_users' => round($percentageTotalUsers, 2),
            'total_subscribers' => $totalSubscribers,
            'percentage_total_subscribers' => round($percentageTotalSubscribers, 2),
            'total_movies' => $totalMovies,
            'percentage_total_movies' => round($percentageTotalMovies, 2),
            'total_series' => $totalSeries,
            'percentage_total_series' => round($percentageTotalSeries, 2),
            'total_views' => $totalViews,
            'percentage_total_views' => round($percentageTotalViews, 2),
            'total_likes' => $totalLikes,
            'percentage_total_likes' => round($percentageTotalLikes, 2),
            'average_view_time' => $averageViewTime,
            'percentage_average_view_time' => round($percentageAverageViewTime, 2),
            'new_content' => $newContent,
            'percentage_new_content' => round($percentageNewContent, 2),
        ]);
    }

    /**
     * Subscribers for last 12 months (rolling 30-day windows)
     */
    public function subscribersRolling()
    {
        $results = [];
        $now = Carbon::now();
        for ($i = 0; $i < 12; $i++) {
            $end = $now->copy()->subMonths($i);
            $start = $end->copy()->subDays(30);
            $count = Subscription::where('status', 'active')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $results[] = [
                'period' => $start->toDateString() . ' - ' . $end->toDateString(),
                'count' => $count
            ];
        }
        return response()->json(array_reverse($results));
    }

    /**
     * User growth for each month for the last 12 months
     */
    public function userGrowth()
    {
        $results = [];
        $now = Carbon::now();
        for ($i = 0; $i < 12; $i++) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $count = User::whereBetween('created_at', [$start, $end])->count();
            $results[] = [
                'month' => $month->format('F Y'),
                'count' => $count
            ];
        }
        return response()->json(array_reverse($results));
    }

    /**
     * Subscriber growth for each month for the last 12 months
     */
    public function subscriberGrowth()
    {
        $results = [];
        $now = Carbon::now();
        for ($i = 0; $i < 12; $i++) {
            $month = $now->copy()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
            $count = Subscription::where('status', 'active')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $results[] = [
                'month' => $month->format('F Y'),
                'count' => $count
            ];
        }
        return response()->json(array_reverse($results));
    }

    /**
     * Transaction history
     */
    public function transactionHistory(Request $request)
    {
        $query = Subscription::with(['user', 'plan'])
            ->orderByDesc('created_at');

        // Optional: add pagination
        $perPage = $request->query('per_page', 50);
        $subscriptions = $query->paginate($perPage);

        $results = $subscriptions->map(function($sub) {
            return [
                'user_id' => $sub->user->id ?? null,
                'name' => $sub->user->first_name . ' ' . $sub->user->last_name,
                'email' => $sub->user->email ?? null,
                'plan' => $sub->plan->name ?? null,
                'date' => $sub->created_at->toDateTimeString(),
                'status' => $sub->status,
                'price' => $sub->plan->amount ?? null,
            ];
        });

        return response()->json([
            'transactions' => $results,
            'pagination' => [
                'total' => $subscriptions->total(),
                'per_page' => $subscriptions->perPage(),
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'from' => $subscriptions->firstItem(),
                'to' => $subscriptions->lastItem()
            ]
        ]);
    }
}