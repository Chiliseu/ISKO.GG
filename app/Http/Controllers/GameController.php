<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function getRecommendation(Request $request)
    {
        $input = trim($request->input('game'));

        if (!$input) {
            return response()->json(["games" => [["name" => "Please enter a game preference."]]]);
        }

        $apiKey = env('RAWG_API_KEY');

        // List of known genres (you can expand this)
        $knownGenres = ['action', 'adventure', 'rpg', 'strategy', 'shooter', 'puzzle', 'horror', 'sports', 'racing', 'indie'];

        // Check if input is a genre or a game title
        if (in_array(strtolower($input), $knownGenres)) {
            $response = Http::get("https://api.rawg.io/api/games", [
                'key' => $apiKey,
                'genres' => strtolower($input), // Use genre filter
                'page_size' => 20,
            ]);
        } else {
            $response = Http::get("https://api.rawg.io/api/games", [
                'key' => $apiKey,
                'search' => $input, // Use title search
                'page_size' => 20,
            ]);
        }

        $data = $response->json();

        if (isset($data['results']) && count($data['results']) > 0) {
            $games = array_map(function ($game) {
                return [
                    'name' => $game['name'],
                    'image' => $game['background_image'] ?? null, // Get game image
                    'rating' => $game['rating'] ?? 'N/A', // Get game rating
                    'platforms' => isset($game['platforms']) 
                        ? implode(", ", array_map(fn($p) => $p['platform']['name'], $game['platforms'])) 
                        : 'Unknown',
                    'genres' => isset($game['genres']) 
                        ? implode(", ", array_map(fn($g) => $g['name'], $game['genres'])) 
                        : 'Unknown'
                ];
            }, $data['results']);

            return response()->json(["games" => $games]);
        }

        return response()->json(["games" => [["name" => "No recommendations found. Try a different keyword!"]]]);
    }
}
