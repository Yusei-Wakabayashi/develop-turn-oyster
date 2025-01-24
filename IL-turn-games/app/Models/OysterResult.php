<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterResult extends Model
{
    use HasFactory;
    protected $table = 'oyster_results';
    protected $fillable = ['game_id','player1', 'player2', 'winner'];

    public function create_result($game_id, $player_1, $player_2, $winner)
    {
        $result = OysterResult::create([
            'game_id' => $game_id,
            'player1' => $player_1,
            'player2' => $player_2,
            'winner' => $winner
        ]);
    }
    // プレイヤーidを基にresult情報を返す
    public function player_result($player_id)
    {
        return OysterResult::where('player1', $player_id)->orWhere('player2', $player_id)->first();
    }
    // gameidを基にresult情報を返す
    public function game_result($game_id)
    {
        return OysterResult::where('game_id', $game_id)->first();
    }
}
