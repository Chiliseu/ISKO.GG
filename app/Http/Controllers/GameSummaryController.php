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

        $gameInput = strtolower(trim($request->input('game')));
        if (!$gameInput) {
            \Log::warning('No game provided.');
            return response()->json(['error' => 'No game provided.'], 400);
        }

        // Check DB
        $existing = GameSummary::where('user_input', $gameInput)->first();
        if ($existing) {
            \Log::info('Found existing summary in DB.', ['summary' => $existing]);
            return response()->json($existing);
        }

        $openaiKey = env('OPENAI_API_KEY');
        $rawgKey = env('RAWG_API_KEY');

        if (!$openaiKey || !$rawgKey) {
            \Log::error('API keys not configured.');
            return response()->json(['error' => 'API keys not configured.'], 500);
        }

        // Fetch RAWG data
        $rawgData = cache()->remember("rawg_game_" . md5($gameInput), 86400, function () use ($rawgKey, $gameInput) {
            $response = Http::get("https://api.rawg.io/api/games", [
                'key' => $rawgKey,
                'search' => $gameInput,
                'page_size' => 1
            ]);

            \Log::info('RAWG API response', ['status' => $response->status()]);
            if ($response->successful()) {
                return $response->json()['results'][0] ?? null;
            }
            return null;
        });

        // Build prompt
        if ($rawgData) {
            $gameName = $rawgData['name'];
            $genres = implode(", ", array_map(fn($g) => $g['name'], $rawgData['genres'] ?? []));
            $platforms = implode(", ", array_map(fn($p) => $p['platform']['name'], $rawgData['platforms'] ?? []));
            $released = $rawgData['released'] ?? 'unknown date';

            $prompt = "You're an expert gamer who summarizes video games. Summarize \"$gameName\" (released on $released for $platforms, genres: $genres). Write an engaging, fun lore/gameplay summary in <150 words.";
        } else {
            $prompt = "You're an expert gamer who *only* summarizes video games. If the title \"$gameInput\" sounds like a game, confidently summarize it. If it doesn't sound like a game, politely say you only summarize games.";
        }

        \Log::info('Prompt prepared.', ['prompt' => $prompt]);

        // Call OpenAI
        $chatResponse = Http::withToken($openaiKey)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-4o",
                "messages" => [
                    ["role" => "system", "content" => "You're a professional gamer who writes fun, confident summaries. Never make up summaries of non-games."],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.7,
                "max_tokens" => 300
            ]);

        \Log::info('OpenAI API response', ['status' => $chatResponse->status()]);

        if ($chatResponse->failed()) {
            \Log::error('Failed to fetch summary from OpenAI.', ['body' => $chatResponse->body()]);
            return response()->json(['error' => 'Failed to fetch summary from OpenAI.'], 500);
        }

        $content = $chatResponse->json()['choices'][0]['message']['content'] ?? null;
        if (!$content) {
            \Log::error('Invalid response from OpenAI.', ['response' => $chatResponse->json()]);
            return response()->json(['error' => 'Invalid response from OpenAI.'], 500);
        }

        // Save
        $saved = GameSummary::updateOrCreate(
            ['user_input' => $gameInput],
            ['summary' => $content]
        );

        \Log::info('Summary saved.', ['saved' => $saved]);

        return response()->json($saved);
    }
}
