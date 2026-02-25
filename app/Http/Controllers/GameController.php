<?php

namespace App\Http\Controllers;

use App\Models\Games;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $query = Games::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('short_description', 'ilike', "%{$search}%")
                  ->orWhere('developer', 'ilike', "%{$search}%")
                  ->orWhere('publisher', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('genre')) {
            $query->where('genre', $request->input('genre'));
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }

        $sortField = $request->input('sort', 'updated_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['title', 'genre', 'platform', 'publisher', 'developer', 'release_date', 'updated_at', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $games = $query->paginate(15)->withQueryString();
        $genres = Games::select('genre')->distinct()->whereNotNull('genre')->orderBy('genre')->pluck('genre');
        $platforms = Games::select('platform')->distinct()->whereNotNull('platform')->orderBy('platform')->pluck('platform');

        return Inertia::render('ManageData', [
            'games' => $games,
            'genres' => $genres,
            'platforms' => $platforms,
            'filters' => $request->only(['search', 'genre', 'platform', 'sort', 'direction']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|string|max:500',
            'short_description' => 'nullable|string',
            'game_url' => 'nullable|string|max:500',
            'genre' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'developer' => 'nullable|string|max:255',
            'release_date' => 'nullable|string|max:50',
            'freetogame_profile_url' => 'nullable|string|max:500',
        ]);

        Games::create($validated);
        return redirect()->back()->with('success', 'Game created successfully.');
    }

    public function update(Request $request, Games $game)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|string|max:500',
            'short_description' => 'nullable|string',
            'game_url' => 'nullable|string|max:500',
            'genre' => 'nullable|string|max:100',
            'platform' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'developer' => 'nullable|string|max:255',
            'release_date' => 'nullable|string|max:50',
            'freetogame_profile_url' => 'nullable|string|max:500',
        ]);

        $game->update($validated);
        return redirect()->back()->with('success', 'Game updated successfully.');
    }

    public function destroy(Games $game)
    {
        $game->delete();
        return redirect()->back()->with('success', 'Game deleted successfully.');
    }
}
