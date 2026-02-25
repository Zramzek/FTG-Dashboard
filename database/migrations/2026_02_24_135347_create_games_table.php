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
            $table->unsignedInteger('api_id')->unique();
            $table->string('title');
            $table->string('thumbnail');
            $table->text('short_description');
            $table->string('game_url');
            $table->string('genre');
            $table->enum('platform', ['PC (Windows)', 'Web Browser']);
            $table->string('publisher');
            $table->string('developer');
            $table->string('release_date');
            $table->string('freetogame_profile_url');
            $table->timestamps();

            $table->index('genre');
            $table->index('platform');
            $table->index('release_date');
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
