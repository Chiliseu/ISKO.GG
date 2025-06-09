<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('user_input');
            $table->longText('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_summaries');
    }
};