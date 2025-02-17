<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterChangeResult extends Model
{
    use HasFactory;
    protected $table = 'oyster_change_results';
    protected $fillable = ['game_id', 'player_id', 'oyster'];
    // ゲームの変更結果を作成
    public function create_change_result($game_id, $player_id, $oyster)
    {
        $result = OysterChangeResult::create([
            'game_id' => $game_id,
            'player_id' => $player_id,
            'oyster' => $oyster
        ]);
        return $result->id;
    }

    // ゲームの変更結果の最新の一件を一つ取得
    public function get_change_result($game_id)
    {
        return OysterChangeResult::where('game_id', $game_id)->orderBy('created_at', 'desc')->first();
    }

    // ゲームの変更結果をgame_idとplayer_idを基に全件取得します
    public function get_all_result($game_id, $player_id)
    {
        return OysterChangeResult::where('game_id', $game_id)->where('player_id', $player_id)->get();
    }
}
