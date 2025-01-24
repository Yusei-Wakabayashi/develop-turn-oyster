<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterPlayer extends Model
{
    use HasFactory;
    protected $table = 'oyster_players';
    protected $fillable = ['player_id', 'status'];

    // プレイヤーを作成
    public function create_player($player_id)
    {
        OysterPlayer::create([
            'player_id' => $player_id,
            'status' => 0
        ]);
    }
    // プレイヤーを取得
    public function get_player($player_id)
    {
        return OysterPlayer::where('player_id', $player_id)->first();
    }
    // プレイヤーのステータスを更新
    public function update_status($player_id, $status)
    {
        $player = OysterPlayer::where('player_id', $player_id)->first();
        $player->status = $status;
        $player->save();
    }
}
