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
        Schema::table('games', function (Blueprint $table) {
            $table->string('slug')->unique()->after('id');
            $table->string('name')->after('slug');
            $table->text('image')->nullable()->after('name');
            $table->string('rating')->nullable()->after('image');
            $table->text('platforms')->nullable()->after('rating');
            $table->text('genres')->nullable()->after('platforms');
            $table->text('matched_genres')->nullable()->after('genres');
            $table->text('trailer_url')->nullable()->after('matched_genres');
            $table->string('youtube_video_id')->nullable()->after('trailer_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
