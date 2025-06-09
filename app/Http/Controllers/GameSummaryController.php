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

        $existing = GameSummary::where('user_input', $game)->first();
        if ($existing) {
            \Log::info('Found existing summary in DB.', ['summary' => $existing]);
            return response()->json($existing);
        }

        $chatApiKey = env('OPENAI_API_KEY');
        $rawgApiKey = env('RAWG_API_KEY');
        \Log::info('API keys loaded.', ['openai' => $chatApiKey ? 'set' : 'missing', 'rawg' => $rawgApiKey ? 'set' : 'missing']);

        if (!$chatApiKey || !$rawgApiKey) {
            \Log::error('API keys not configured.');
            return response()->json(['error' => 'API keys not configured.'], 500);
        }

        // Fetch game details from RAWG API
        $gameDataResponse = Http::get("https://api.rawg.io/api/games", [
            'key' => $rawgApiKey,
            'search' => $game,
            'page_size' => 1
        ]);
        \Log::info('RAWG API response', ['status' => $gameDataResponse->status(), 'body' => $gameDataResponse->body()]);

        $gameData = $gameDataResponse->json();

        if (empty($gameData['results'])) {
            \Log::warning('Game not found in RAWG API.', ['search' => $game]);
            return response()->json(['error' => 'Game not found.'], 404);
        }

        $gameInfo = $gameData['results'][0];
        $gameName = $gameInfo['name'];
        \Log::info('Game found.', ['gameName' => $gameName]);

        $summaryPrompt = "Give a concise, fun-to-read lore/gameplay summary of the game \"$gameName\" in less than 150 words.";
        \Log::info('Summary prompt created.', ['prompt' => $summaryPrompt]);

        // Call OpenAI ChatGPT API
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
            \Log::error('Failed to fetch summary from ChatGPT.', ['status' => $chatResponse->status(), 'body' => $chatResponse->body()]);
            return response()->json([
                'error' => 'Failed to fetch summary from ChatGPT.',
                'status' => $chatResponse->status(),
                'body' => $chatResponse->body()
            ], 500);
        }

        $responseData = $chatResponse->json();
        \Log::info('OpenAI responseData parsed.', ['responseData' => $responseData]);

        if (!isset($responseData['choices'][0]['message']['content'])) {
            \Log::error('Invalid response structure from ChatGPT.', ['responseData' => $responseData]);
            return response()->json([
                'error' => 'Invalid response structure from ChatGPT.',
                'body' => $responseData
            ], 500);
        }

        $summary = $responseData['choices'][0]['message']['content'];
        \Log::info('Summary generated.', ['summary' => $summary]);

        // Save summary to database
        $saved = GameSummary::create([
            'user_input' => $game,
            'summary' => $summary,
        ]);
        \Log::info('Summary saved to DB.', ['saved' => $saved]);

        return response()->json($saved);
    }
}