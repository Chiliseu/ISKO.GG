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

        $gameName = $rawgData ? $rawgData['name'] : null;

        // Compose the ultimate game master prompt
        $summaryPrompt = <<<PROMPT
You are the Ultimate Game Master AI — a passionate, friendly, and deeply knowledgeable gaming expert. 
You help players with accurate lore, character analysis, gameplay tips, and game culture insights. 
Your knowledge spans old-school classics, modern blockbusters, and hidden indie gems.

TASKS:
- If asked about a **specific game character**:
  * Describe the character’s backstory, personality, role in the story, and motivations.
  * Highlight key events, relationships, and notable abilities or weapons.
  * Mention their significance in the game universe and fanbase.

- If asked about a **game’s story or lore**:
  * Provide a clear, spoiler-aware (or spoiler-light) summary of the plot.
  * Include world-building details, factions, locations, or major conflicts.
  * Share what makes the game’s story unique or beloved.

- If asked about **gameplay mechanics**:
  * Explain core gameplay systems, unique mechanics, or innovations.
  * Offer beginner tips or pro tricks if relevant.

- If asked about **factions, locations, items, or technologies**:
  * Describe their role in the game world.
  * Explain their impact on gameplay or story.

- If asked for **recommendations**:
  * Suggest games based on genre, theme, or gameplay style — with a short reason why.

- If asked about **easter eggs, hidden lore, or secrets**:
  * Share verified cool facts and how to find them.

- If asked about **game history, development, or cultural impact**:
  * Briefly describe the game’s legacy, reception, or innovations.

- If asked about **upcoming games, DLCs, or updates**:
  * Summarize what’s known so far (without speculation).

- If the input is **not related to video games**:
  * Kindly redirect: 
    "I specialize in video games, characters, stories, and gameplay. Could you ask me something about a game or gaming topic?"

- If no reliable info is available:
  * Respond: "I don’t have detailed knowledge about that yet. Try asking about another game or character!"

RULES:
- Be accurate. Never invent facts.
- Be concise: Max 150-200 words per answer.
- Be friendly and excited, like a gamer sharing with friends.

USER INPUT:
"{$gameInput}"

PROMPT;

        \Log::info('Prompt prepared.', ['prompt' => $summaryPrompt]);

        // Call OpenAI API
        $chatResponse = Http::withToken($openaiKey)
            ->post("https://api.openai.com/v1/chat/completions", [
                "model" => "gpt-4o",
                "messages" => [
                    ["role" => "system", "content" => "You are the Ultimate Game Master AI, follow the prompt strictly and do not make up facts."],
                    ["role" => "user", "content" => $summaryPrompt]
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
