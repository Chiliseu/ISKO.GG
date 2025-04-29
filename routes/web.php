<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

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

// ✅ NEW: Route to view individual game details by slug
Route::get('/community/{slug}', [GameController::class, 'showGameDetails'])->name('community.details');
