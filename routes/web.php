<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

// Ensure both "/" and "/home" point to the same named route to prevent mismatches
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/home', function () {
    return redirect()->route('home'); // Redirect /home to /
});

// Define the route for game recommendations (POST request)
Route::post('/get-recommendation', [GameController::class, 'getRecommendation'])
    ->name('get.recommendation');
