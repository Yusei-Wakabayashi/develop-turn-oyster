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
        Schema::create('oyster_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('player1',32)->unique()->nullable();
            $table->foreign('player1')->references('player_id')->on('oyster_players');
            $table->string('player2',32)->unique()->nullable();
            $table->foreign('player2')->references('player_id')->on('oyster_players');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oyster_rooms');
    }
};
