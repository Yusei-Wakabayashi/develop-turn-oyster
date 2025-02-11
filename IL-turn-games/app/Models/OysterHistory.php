<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterHistory extends Model
{
    use HasFactory;
    protected $table = 'oyster_historys';
    protected $fillable = ['game_id', 'player1', 'player2', 'player1_board', 'player2_board', 'turn_count'];
    // ゲームの履歴を作成
    public function create_history($game_id, $player1, $player2, $player1_board, $player2_board, $turn_count)
    {
        $history = OysterHistory::create([
            'game_id' => $game_id,
            'player1' => $player1,
            'player2' => $player2,
            'player1_board' => $player1_board,
            'player2_board' => $player2_board,
            'turn_count' => $turn_count
        ]);
        return $history->id;
    }
}
