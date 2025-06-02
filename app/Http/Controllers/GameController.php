<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function getRecommendation(Request $request)
    {
        $input = trim($request->input('game'));

        if (!$input) {
            return response()->json([
                "games" => [
                    ["name" => "Please enter a game preference."]
                ]
            ]);
        }

        $apiKey = env('RAWG_API_KEY');
        $youtubeApiKey = env('YOUTUBE_API_KEY');

        // Fetch genres
        $genresResponse = Http::get("https://api.rawg.io/api/genres", [
            'key' => $apiKey
        ]);

        if ($genresResponse->failed()) {
            return response()->json([
                "games" => [
                    ["name" => "Failed to fetch genres."]
                ]
            ]);
        }

        $genresData = $genresResponse->json();
        $availableGenres = array_map(fn($genre) => strtolower($genre['name']), $genresData['results']);

        // Search games
        $response = Http::get("https://api.rawg.io/api/games", [
            'key' => $apiKey,
            'search' => $input,
            'page_size' => 25,
        ]);

        $data = $response->json();

        if (!isset($data['results']) || count($data['results']) === 0) {
            return response()->json([
                "games" => [
                    ["name" => "No recommendations found. Try a different keyword!"]
                ]
            ]);
        }

        $games = array_map(function ($game) use ($availableGenres, $apiKey, $youtubeApiKey) {
            $slug = $game['slug'];

            // Check if the game already exists
            $existing = Game::where('slug', $slug)->first();
            if ($existing) {
                return [
                    'name' => $existing->name,
                    'image' => $existing->image,
                    'rating' => $existing->rating,
                    'platforms' => $existing->platforms,
                    'genres' => $existing->genres,
                    'matchedGenres' => $existing->matched_genres,
                    'trailer' => $existing->trailer_url,
                    'youtubeVideoId' => $existing->youtube_video_id,
                ];
            }

            $gameGenres = isset($game['genres']) 
                ? implode(", ", array_map(fn($g) => $g['name'], $game['genres']))
                : 'Unknown';

            $matchedGenres = array_filter(explode(", ", $gameGenres), function($genre) use ($availableGenres) {
                return in_array(strtolower(trim($genre)), $availableGenres);
            });

            $trailer = null;
            $videoId = null;

            // Try RAWG trailer
            $trailerResponse = Http::get("https://api.rawg.io/api/games/{$slug}/movies", [
                'key' => $apiKey
            ]);

            if ($trailerResponse->successful()) {
                $videos = $trailerResponse->json()['results'] ?? [];
                foreach ($videos as $video) {
                    if (stripos($video['name'], 'trailer') !== false && isset($video['data']['max'])) {
                        $trailer = $video['data']['max'];
                        break;
                    }
                }

                if (!$trailer && !empty($videos)) {
                    $trailer = $videos[0]['data']['max'] ?? null;
                }
            }

            // Fallback to YouTube
            if (!$trailer) {
                $searchQueries = [
                    $game['name'] . ' official trailer gameplay',
                    $game['name'] . ' trailer',
                    $game['name'] . ' gameplay'
                ];

                foreach ($searchQueries as $query) {
                    $youtubeResponse = Http::get("https://www.googleapis.com/youtube/v3/search", [
                        'key' => $youtubeApiKey,
                        'q' => $query,
                        'part' => 'snippet',
                        'maxResults' => 5,
                        'type' => 'video',
                        'videoEmbeddable' => 'true',
                        'safeSearch' => 'strict'
                    ]);

                    if ($youtubeResponse->successful()) {
                        $youtubeData = $youtubeResponse->json();
                        foreach ($youtubeData['items'] as $item) {
                            $videoId = $item['id']['videoId'] ?? null;
                            if ($videoId) {
                                $trailer = "https://www.youtube.com/embed/{$videoId}";
                                break 2;
                            }
                        }
                    }
                }
            }

            // Save to DB
            $newGame = Game::create([
                'slug' => $slug,
                'name' => $game['name'],
                'image' => $game['background_image'] ?? null,
                'rating' => $game['rating'] ?? 'N/A',
                'platforms' => isset($game['platforms']) 
                    ? implode(", ", array_map(fn($p) => $p['platform']['name'], $game['platforms'])) 
                    : 'Unknown',
                'genres' => $gameGenres,
                'matched_genres' => implode(", ", $matchedGenres),
                'trailer_url' => $trailer,
                'youtube_video_id' => $videoId
            ]);

            return [
                'name' => $newGame->name,
                'image' => $newGame->image,
                'rating' => $newGame->rating,
                'platforms' => $newGame->platforms,
                'genres' => $newGame->genres,
                'matchedGenres' => $newGame->matched_genres,
                'trailer' => $newGame->trailer_url,
                'youtubeVideoId' => $newGame->youtube_video_id,
            ];
        }, $data['results']);

        return response()->json(["games" => $games]);
    }

    public function searchGames(Request $request)
    {
        $query = $request->input('q');
        $apiKey = env('RAWG_API_KEY');

        if (!$query) return response()->json([]);

        $response = Http::get('https://api.rawg.io/api/games', [
            'key' => $apiKey,
            'search' => $query,
            'page_size' => 10
        ]);

        if ($response->failed()) return response()->json([]);

        $data = $response->json();
        $games = collect($data['results'])->map(function ($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'image' => $game['background_image'] ?? null,
                'rating' => $game['rating'] ?? 'N/A',
                'slug' => $game['slug']
            ];
        });

        return response()->json($games);
    }
}
