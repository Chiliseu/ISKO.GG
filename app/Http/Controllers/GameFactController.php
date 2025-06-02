<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameFactController extends Controller
{
    public function random()
    {
        $fact = DB::table('game_facts')->inRandomOrder()->first();

        if (!$fact) {
            return response()->json(['error' => 'No facts found.'], 404);
        }

        return response()->json([
            'fact' => $fact->fact, // correct column
            'image' => asset($fact->image_path),
        ]);
    }
}
