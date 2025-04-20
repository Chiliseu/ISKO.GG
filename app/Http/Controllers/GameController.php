<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // Fetch available genres from RAWG API
        $genresResponse = Http::get("https://api.rawg.io/api/genres", [
            'key' => $apiKey
        ]);

        if ($genresResponse->failed()) {
            return response()->json([
                "games" => [
                    ["name" => "Failed to fetch available genres."]
                ]
            ]);
        }

        $genresData = $genresResponse->json();
        $availableGenres = array_map(fn($genre) => strtolower($genre['name']), $genresData['results']);

        // Search for games
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
            $gameGenres = isset($game['genres']) 
                ? implode(", ", array_map(fn($g) => $g['name'], $game['genres']))
                : 'Unknown';

            $matchedGenres = array_filter(explode(", ", $gameGenres), function($genre) use ($availableGenres) {
                return in_array(strtolower(trim($genre)), $availableGenres);
            });

            $trailer = null;
            $videoId = null;

            // Try to get RAWG trailer
            if (isset($game['slug'])) {
                $trailerResponse = Http::get("https://api.rawg.io/api/games/{$game['slug']}/movies", [
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
            }

            // Fallback to YouTube embed
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
                        Log::info("YouTube Search Query: " . $query);
                        Log::info("YouTube Response: ", $youtubeData);

                        foreach ($youtubeData['items'] as $item) {
                            $videoId = $item['id']['videoId'] ?? null;
                            if ($videoId) {
                                $trailer = "https://www.youtube.com/embed/{$videoId}";
                                break 2; // Break out of both foreach loops
                            }
                        }
                    }
                }
            }

            return [
                'name' => $game['name'],
                'image' => $game['background_image'] ?? null,
                'rating' => $game['rating'] ?? 'N/A',
                'platforms' => isset($game['platforms']) 
                    ? implode(", ", array_map(fn($p) => $p['platform']['name'], $game['platforms'])) 
                    : 'Unknown',
                'genres' => $gameGenres,
                'matchedGenres' => implode(", ", $matchedGenres),
                'url' => 'https://rawg.io/games/' . $game['slug'],
                'trailer' => $trailer,
                'youtubeVideoId' => $videoId // optional, only set if YouTube is used
            ];
        }, $data['results']);

        return response()->json(["games" => $games]);
    }
}
