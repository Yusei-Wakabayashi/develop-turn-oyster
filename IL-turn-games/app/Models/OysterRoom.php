<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterRoom extends Model
{
    use HasFactory;
    protected $table = 'oyster_rooms';
    protected $fillable = ['player1', 'player2', 'status'];

    // public function search_room($player_id)
    // {
    //     return OysterRoom::where('player_1', $player_id)->orWhere('player_2', $player_id)->first();
    // }

    // ルームの状態がstatusと一致するレコード先頭一件を取得
    public function search_status($status)
    {
        return OysterRoom::where('status', $status)->first();
    }

    // ルームにプレイヤーがいるかどうかを確認
    public function search_player($player_id)
    {
        return OysterRoom::where('player1', $player_id)->orWhere('player2', $player_id)->first();
    }

    // ルームの状態を更新
    public function update_status($room_id, $status)
    {
        // ルームidと一致するレコードを取得
        $room = OysterRoom::where('id', $room_id)->first();
        $room->status = $status;
        $room->save();
    }
    // ルームにプレイヤーを追加
    public function update_player($room_id, $player_id)
    {
        // ルームidと一致するレコードを取得
        $room = OysterRoom::where('id', $room_id)->first();
        // プレイヤー1が空の場合
        if($room->player1 == null){
            // プレイヤー1にプレイヤーを追加
            $room->player1 = $player_id;
            // ルームのステータスを1(待機中)に変更
            $room->status = 1;
        }else{
            // プレイヤー2にプレイヤーを追加
            $room->player2 = $player_id;
            // ルームのステータスを2(満室)に変更
            $room->status = 2;
        }
        $room->save();
    }

    // ルームを初期化
    public function reset_room($room_id)
    {
        // ルームidと一致するレコードを取得
        $room = OysterRoom::where('id', $room_id)->first();
        // プレイヤー1とプレイヤー2をnullに変更
        $room->player1 = null;
        $room->player2 = null;
        // ルームのステータスを0(空き)に変更
        $room->status = 0;
        $room->save();
    }
}
