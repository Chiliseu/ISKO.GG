<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('game_slug')->unique(); // Slug from the games table
            $table->longText('summary');           // The ChatGPT-generated summary
            $table->text('image_url')->nullable(); // Optional image related to the game
            $table->timestamps();                  // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_summaries');
    }
};
