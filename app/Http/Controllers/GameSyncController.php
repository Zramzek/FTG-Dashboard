<?php

namespace App\Http\Controllers;

use App\Models\Games;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GameSyncController extends Controller
{
    public function sync()
    {
        try {
            $response = Http::timeout(30)->get('https://www.freetogame.com/api/games');

            if (!$response->successful()) {
                return redirect()->back()->with('error', 'Failed to fetch data from FreeToGame API.');
            }

            $apiGames = $response->json();
            $synced = 0;
            $updated = 0;

            foreach ($apiGames as $apiGame) {
                $existing = Games::where('api_id', $apiGame['id'])->first();

                $data = [
                    'api_id' => $apiGame['id'],
                    'title' => $apiGame['title'],
                    'thumbnail' => $apiGame['thumbnail'] ?? null,
                    'short_description' => $apiGame['short_description'] ?? null,
                    'game_url' => $apiGame['game_url'] ?? null,
                    'genre' => $apiGame['genre'] ?? null,
                    'platform' => $apiGame['platform'] ?? null,
                    'publisher' => $apiGame['publisher'] ?? null,
                    'developer' => $apiGame['developer'] ?? null,
                    'release_date' => $apiGame['release_date'] ?? null,
                    'freetogame_profile_url' => $apiGame['freetogame_profile_url'] ?? null,
                ];

                if ($existing) {
                    $changed = false;
                    foreach ($data as $key => $value) {
                        if ($existing->{$key} != $value) {
                            $changed = true;
                            break;
                        }
                    }
                    if ($changed) {
                        $existing->update($data);
                        $updated++;
                    }
                } else {
                    Games::create($data);
                    $synced++;
                }
            }

            Cache::put('last_sync_time', now()->toIso8601String(), now()->addYear());

            return redirect()->back()->with('success', "Sync complete. Created: {$synced}, Updated: {$updated}");
        } catch (\Exception $e) {
            Log::error('Game sync failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function lastSync()
    {
        return response()->json([
            'last_sync_time' => Cache::get('last_sync_time'),
        ]);
    }
}
