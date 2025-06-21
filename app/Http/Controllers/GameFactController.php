<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameFactController extends Controller
{
    public function random()
    {
        \Log::info('GameFactController@random called.');

        try {
            $fact = DB::table('game_facts')->inRandomOrder()->first();
            \Log::info('DB query executed.', ['fact' => $fact]);

            if (!$fact) {
                \Log::error('No facts found in game_facts table.');
                return response()->json(['error' => 'No facts found.'], 404);
            }

            \Log::info('Returning fact and image.', ['fact' => $fact->fact, 'image' => $fact->image_path]);
            return response()->json([
                'fact' => $fact->fact,
                'image' => asset($fact->image_path),
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception in GameFactController@random', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error.'], 500);
        }
    }
}