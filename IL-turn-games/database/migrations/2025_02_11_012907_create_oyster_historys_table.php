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
        Schema::create('oyster_historys', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->nullable();
            $table->string('player1',32)->nullable();
            $table->string('player2',32)->nullable();
            $table->string('player1_board', 24)->default('000000000000000000000000');
            $table->string('player2_board', 24)->default('000000000000000000000000');
            $table->integer('turn_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oyster_historys');
    }
};
