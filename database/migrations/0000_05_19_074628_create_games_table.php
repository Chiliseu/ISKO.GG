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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('image')->nullable();
            $table->string('rating')->nullable();
            $table->text('platforms')->nullable();
            $table->text('genres')->nullable();
            $table->text('matched_genres')->nullable();
            $table->text('trailer_url')->nullable();
            $table->string('youtube_video_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'name',
                'image',
                'rating',
                'platforms',
                'genres',
                'matched_genres',
                'trailer_url',
                'youtube_video_id'
            ]);
        });
    }
};
