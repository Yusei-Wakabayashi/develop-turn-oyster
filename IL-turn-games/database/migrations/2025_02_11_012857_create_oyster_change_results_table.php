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
        Schema::create('oyster_change_results', function (Blueprint $table) {
            $table->id();
            $table->integer('game_id')->nullable();
            $table->string('player_id',32)->nullable();
            $table->integer('oyster')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oyster_change_results');
    }
};
