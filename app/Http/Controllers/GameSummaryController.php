<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GameSummary;

class GameSummaryController extends Controller
{
    public function summarize(Request $request)
    {
        $game = trim($request->input('game'));

        if (!$game) {
            return response()->json(['error' => 'No game provided.'], 400);
        }

        // Check if summary already exists
        $existing = GameSummary::where('game_slug', strtolower($game))->first();
        if ($existing) {
            return response()->json($existing);
        }

        $chatApiKey = env('OPENAI_API_KEY');
        $rawgApiKey = env('RAWG_API_KEY');

        if (!$chatApiKey || !$rawgApiKey) {
            return response()->json(['error' => 'API keys not configured.'], 500);
        }

        // Fetch game details from RAWG API
        $gameData = Http::get("https://api.rawg.io/api/games", [
            'key' => $rawgApiKey,
            'search' => $game,
            'page_size' => 1
        ])->json();

        if (empty($gameData['results'])) {
            return response()->json(['error' => 'Game not found.'], 404);
        }

        $gameInfo = $gameData['results'][0];
        $gameName = $gameInfo['name'];
        $gameSlug = $gameInfo['slug'];
        $gameImage = $gameInfo['background_image'] ?? null;

        $summaryPrompt = "Give a concise, fun-to-read lore/gameplay summary of the game \"$gameName\" in less than 150 words.";

        // Call OpenAI ChatGPT API
        $chatResponse = Http::withToken($chatApiKey)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-3.5-turbo",
                "messages" => [
                    ["role" => "system", "content" => "You are a gaming expert summarizer."],
                    ["role" => "user", "content" => $summaryPrompt]
                ],
                "temperature" => 0.7,
                "max_tokens" => 300
            ]);

        if ($chatResponse->failed()) {
            return response()->json([
                'error' => 'Failed to fetch summary from ChatGPT.',
                'status' => $chatResponse->status(),
                'body' => $chatResponse->body()
            ], 500);
        }

        $responseData = $chatResponse->json();

        if (!isset($responseData['choices'][0]['message']['content'])) {
            return response()->json([
                'error' => 'Invalid response structure from ChatGPT.',
                'body' => $responseData
            ], 500);
        }

        $summary = $responseData['choices'][0]['message']['content'];

        // Save summary to database
        $saved = GameSummary::create([
            'game_slug' => $gameSlug,
            'summary' => $summary,
            'image_url' => $gameImage
        ]);

        return response()->json($saved);
    }
}
