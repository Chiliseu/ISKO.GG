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
        \Log::info('User input:', ['game' => $gameInput]);

        if (!$gameInput) {
            \Log::warning('No game provided.');
            return response()->json(['error' => 'No game provided.'], 400);
        }

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

        // Try to match a game from RAWG
        $rawgData = cache()->remember("rawg_game_" . md5($gameInput), 86400, function () use ($rawgKey, $gameInput) {
            $response = Http::get("https://api.rawg.io/api/games", [
                'key' => $rawgKey,
                'search' => $gameInput,
                'page_size' => 1
            ]);
            return $response->successful() ? ($response->json()['results'][0] ?? null) : null;
        });

        $prompt = '';

        if ($rawgData) {
            $gameName = $rawgData['name'];
            \Log::info('Game matched from RAWG.', ['gameName' => $gameName]);

            // Check if input mentions more than just the game
            if (!preg_match("/\b" . preg_quote(strtolower($gameName), '/') . "\b/", $gameInput)) {
                $prompt = <<<PROMPT
You're a professional gaming expert. The user asked:
"{$gameInput}"

If this relates to a character, lore, or specific element of the game "{$gameName}", provide a fun, compact summary (under 150 words) focusing on that. Include key connections to the game world.

If the input doesn't relate to gaming, politely say:
"I'm a gamer expert and I only summarize actual video games and their content."
PROMPT;
            } else {
                $prompt = <<<PROMPT
You're a professional gaming expert. Write a fun, engaging, compact summary (under 150 words) of the game "{$gameName}". Include lore, main characters, and gameplay elements.
PROMPT;
            }
        } else {
            \Log::warning('No RAWG match found.');
            $prompt = <<<PROMPT
You're a professional gaming expert. The user asked:
"{$gameInput}"

If this is about a video game or a character in a game, provide a concise (under 150 words) lore/gameplay summary.

If it's unrelated to gaming, politely say:
"I'm a gamer expert and I only summarize actual video games and their content."
PROMPT;
        }

        \Log::info('Prompt prepared.', ['prompt' => $prompt]);

        // Call OpenAI
        $chatResponse = Http::withToken($openaiKey)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-4o",
                "messages" => [
                    ["role" => "system", "content" => "You are a gaming expert summarizer. Do not make up facts. If input isn't about games, politely decline."],
                    ["role" => "user", "content" => $prompt]
                ],
                "temperature" => 0.7,
                "max_tokens" => 300
            ]);

        if ($chatResponse->failed()) {
            \Log::error('OpenAI request failed.', ['status' => $chatResponse->status(), 'body' => $chatResponse->body()]);
            return response()->json(['error' => 'Failed to get summary from AI.'], 500);
        }

        $responseData = $chatResponse->json();

        if (!isset($responseData['choices'][0]['message']['content'])) {
            \Log::error('OpenAI invalid response.', ['responseData' => $responseData]);
            return response()->json(['error' => 'Invalid AI response.'], 500);
        }

        $summary = $responseData['choices'][0]['message']['content'];
        \Log::info('Summary generated.', ['summary' => $summary]);

        // Save to DB
        $saved = GameSummary::create([
            'user_input' => $gameInput,
            'summary' => $summary
        ]);

        return response()->json($saved);
    }
}
