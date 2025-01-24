<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OysterGame extends Model
{
    use HasFactory;
    protected $table = 'oyster_games';
    protected $fillable = ['player1', 'player2', 'player1_board', 'player2_board', 'turn', 'first_turn', 'status'];
    // ゲームを作成
    public function create_game($player_1, $player_2)
    {
        $game = OysterGame::create([
            'player1' => $player_1,
            'player2' => $player_2,
            'player1_board' => '000000000000000000000000',
            'player2_board' => '000000000000000000000000',
            'turn' => 1,
            'first_turn' => rand(0, 1),
            'status' => 0
        ]);
        // ゲームのIDを返す
        return $game->id;
    }
    // ゲームを削除
    public function delete_game($game_id)
    {
        OysterGame::where('id', $game_id)->delete();
    }
    // ゲームを取得
    public function get_game($game_id)
    {
        return OysterGame::where('id', $game_id)->first();
    }
    // プレイヤーが参加しているゲームを取得
    public function player_game($player_id)
    {
        return OysterGame::where('player1', $player_id)->orWhere('player2', $player_id)->first();
    }
    // ボードを更新
    public function update_board($game_id, $player_id, $board)
    {
        $game = OysterGame::where('id', $game_id)->first();
        if($game->player1 == $player_id)
        {
            $game->player1_board = $board;
        }
        else if($game->player2 == $player_id)
        {
            $game->player2_board = $board;
        }
        $game->save();
    }
    // ステータスを更新
    public function update_status($game_id, $status)
    {
        $game = OysterGame::where('id', $game_id)->first();
        $game->status = $status;
        $game->save();
    }
    // ターンを更新
    public function update_turn($game_id, $status)
    {
        $game = OysterGame::where('id', $game_id)->first();
    }
    // プレイヤーidを基にどちらをplyaer_1、player_2として更新するか判断する
    public function update_info($player_id, $game_id, $my_board, $enemy_board, $turn)
    {
        $game = OysterGame::where('id', $game_id)->first();
        if($game->player1 == $player_id)
        {
            $game->player1_board = $my_board;
            $game->player2_board = $enemy_board;
            $game->turn = $turn;
        }
        else if($game->player2 == $player_id)
        {
            $game->player2_board = $my_board;
            $game->player1_board = $enemy_board;
            $game->turn = $turn;
        }
        $game->save();
    }
}
