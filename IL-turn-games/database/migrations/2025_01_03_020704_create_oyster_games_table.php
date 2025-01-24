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
        Schema::create('oyster_games', function (Blueprint $table) {
            $table->id();
            $table->string('player1',32)->unique();
            $table->foreign('player1')->references('player_id')->on('oyster_players');
            $table->string('player2',32)->unique();
            $table->foreign('player2')->references('player_id')->on('oyster_players');
            $table->string('player1_board', 24)->default('000000000000000000000000');
            $table->string('player2_board', 24)->default('000000000000000000000000');
            $table->integer('turn')->nullable();
            $table->integer('first_turn')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oyster_games');
    }
};
