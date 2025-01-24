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
        Schema::create('oyster_results', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->unique();
            $table->string('player1',32)->nullable();
            $table->string('player2',32)->nullable();
            $table->integer('winner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oyster_results');
    }
};
