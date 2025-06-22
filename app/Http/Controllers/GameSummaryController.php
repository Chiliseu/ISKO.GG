<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GameSummary;

class GameSummaryController extends Controller
{
    public function summarize(Request $request)
    {
        \Log::info('Summarize endpoint called.', ['request' => $request->all()]);

        $game = strtolower(trim($request->input('game')));
        \Log::info('User input:', ['game' => $game]);

        if (!$game) {
            \Log::warning('No game provided.');
            return response()->json(['error' => 'No game provided.'], 400);
        }

        // Check if summary already exists
        $existing = GameSummary::where('user_input', $game)->first();
        if ($existing) {
            \Log::info('Found existing summary in DB.', ['summary' => $existing]);
            return response()->json($existing);
        }

        $chatApiKey = env('OPENAI_API_KEY');
        $rawgApiKey = env('RAWG_API_KEY');

        if (!$chatApiKey || !$rawgApiKey) {
            \Log::error('API keys not configured.');
            return response()->json(['error' => 'API keys not configured.'], 500);
        }

        // Use cached RAWG result if available
        $gameInfo = cache()->remember("rawg_game_" . md5($game), 86400, function () use ($rawgApiKey, $game) {
            $response = Http::get("https://api.rawg.io/api/games", [
                'key' => $rawgApiKey,
                'search' => $game,
                'page_size' => 1
            ]);

            \Log::info('RAWG API response', ['status' => $response->status(), 'body' => $response->body()]);
            
            if ($response->failed()) {
                return null;
            }

            $results = $response->json()['results'] ?? [];
            return $results[0] ?? null;
        });

        if (!$gameInfo) {
            \Log::warning('Game not found in RAWG API.', ['search' => $game]);
            return response()->json(['error' => 'Game not found.'], 404);
        }

        // Verify title similarity
        if (stripos($gameInfo['name'], $game) === false) {
            \Log::warning('Best match does not closely match input.', ['input' => $game, 'matched' => $gameInfo['name']]);
            return response()->json(['error' => 'No close match found.'], 404);
        }

        $gameName = $gameInfo['name'];

        // Improved prompt
        $summaryPrompt = "You are a professional gaming expert. Provide a concise, engaging lore or gameplay summary for the official video game \"$gameName\" in less than 150 words. If the game does not exist or you have no information about it, say: 'Sorry, I couldn't find details about this game.' Do not make up any facts.";

        \Log::info('Summary prompt created.', ['prompt' => $summaryPrompt]);

        // Call OpenAI
        $chatResponse = Http::withToken($chatApiKey)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-4o",
                "messages" => [
                    ["role" => "system", "content" => "You are a gaming expert summarizer."],
                    ["role" => "user", "content" => $summaryPrompt]
                ],
                "temperature" => 0.7,
                "max_tokens" => 300
            ]);

        \Log::info('OpenAI API response', ['status' => $chatResponse->status(), 'body' => $chatResponse->body()]);

        if ($chatResponse->failed()) {
            \Log::error('Failed to fetch summary from ChatGPT.', ['status' => $chatResponse->status()]);
            return response()->json(['error' => 'Failed to fetch summary from ChatGPT.'], 500);
        }

        $responseData = $chatResponse->json();
        $summary = $responseData['choices'][0]['message']['content'] ?? null;

        if (!$summary) {
            \Log::error('Invalid response structure from ChatGPT.', ['responseData' => $responseData]);
            return response()->json(['error' => 'Invalid response from ChatGPT.'], 500);
        }

        \Log::info('Summary generated.', ['summary' => $summary]);

        // Save or update DB
        $saved = GameSummary::updateOrCreate(
            ['user_input' => $game],
            ['summary' => $summary]
        );

        \Log::info('Summary saved to DB.', ['saved' => $saved]);

        return response()->json($saved);
    }
}
