<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameSummaryController;
use App\Http\Controllers\GameFactController;
use App\Http\Controllers\MusicController;
use Illuminate\Support\Facades\Http;

Route::get('/random-music', [MusicController::class, 'getRandomMusic'])->name('random.music');

Route::get('/test-openai', function () {
    $response = Http::withToken(env('OPENAI_API_KEY'))
        ->post('https://api.openai.com/v1/chat/completions', [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant."],
                ["role" => "user", "content" => "Say hello."]
            ],
            "max_tokens" => 10
        ]);
    return $response->json();
});


// Route for the main homepage
Route::get('/', function () {
    return view('home');
})->name('home');

// Redirect /home to /
Route::get('/home', function () {
    return redirect()->route('home');
});

// Route to fetch game recommendations (POST from JS)
Route::post('/get-recommendation', [GameController::class, 'getRecommendation'])->name('get.recommendation');

// Route to show the community page
Route::get('/community', [GameController::class, 'communityView'])->name('community');

// Route for live search (AJAX as-you-type search from JS)
Route::get('/community/search', [GameController::class, 'searchGames'])->name('community.search');

// Route to view individual game details by slug
Route::get('/community/{slug}', [GameController::class, 'showGameDetails'])->name('community.details');

// Route for ChatGPT API
Route::post('/summarize', [GameSummaryController::class, 'summarize'])->name('summarize');

// Route for Game Facts
Route::get('/random-fact', [App\Http\Controllers\GameFactController::class, 'random']);



