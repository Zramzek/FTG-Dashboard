<?php

namespace App\Http\Controllers;

use App\Models\Games;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->input('range', 'all');

        $rangeMap = [
            '1m' => now()->subMonth(),
            '3m' => now()->subMonths(3),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            '2y' => now()->subYears(2),
            '3y' => now()->subYears(3),
            '5y' => now()->subYears(5),
        ];

        $dateFrom = $rangeMap[$range] ?? null;

        $totalGames = Games::count();
        $totalGenres = Games::whereNotNull('genre')->distinct('genre')->count('genre');
        $totalPlatforms = Games::whereNotNull('platform')->distinct('platform')->count('platform');

        $topGenre = Games::select('genre', DB::raw('count(*) as count'))
            ->whereNotNull('genre')
            ->groupBy('genre')
            ->orderByDesc('count')
            ->first();

        $latestGame = Games::orderByDesc('release_date')->first();

        $genreQuery = Games::select('genre', DB::raw('count(*) as count'))
            ->whereNotNull('genre');
        if ($dateFrom) $genreQuery->where('release_date', '>=', $dateFrom->format('Y-m-d'));
        $genreDistribution = $genreQuery->groupBy('genre')->orderByDesc('count')->get();

        $platformQuery = Games::select('platform', DB::raw('count(*) as count'))
            ->whereNotNull('platform');
        if ($dateFrom) $platformQuery->where('release_date', '>=', $dateFrom->format('Y-m-d'));
        $platformDistribution = $platformQuery->groupBy('platform')->orderByDesc('count')->get();

        $gamesPerMonthQuery = Games::select(
                DB::raw("TO_CHAR(TO_DATE(release_date, 'YYYY-MM-DD'), 'YYYY-MM') as month"),
                DB::raw('count(*) as count')
            )
            ->whereNotNull('release_date')
            ->where('release_date', '!=', '');
        if ($dateFrom) $gamesPerMonthQuery->where('release_date', '>=', $dateFrom->format('Y-m-d'));
        $gamesPerMonth = $gamesPerMonthQuery->groupBy('month')->orderBy('month')->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalGames' => $totalGames,
                'totalGenres' => $totalGenres,
                'totalPlatforms' => $totalPlatforms,
                'topGenre' => $topGenre,
                'latestGame' => $latestGame,
            ],
            'genreDistribution' => $genreDistribution,
            'platformDistribution' => $platformDistribution,
            'gamesPerMonth' => $gamesPerMonth,
            'filters' => ['range' => $range],
            'lastSyncTime' => Cache::get('last_sync_time'),
        ]);
    }
}
