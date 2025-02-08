<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OysterPlayer;
use App\Models\OysterRoom;
use App\Models\OysterGame;
use App\Models\OysterResult;
use App\Events\OysterPreparation;
use App\Events\OysterStart;
use App\Events\OysterTurnEnd;
use App\Events\OysterEndGame;

class OysterController extends Controller
{
    public function show_request(Request $request)
    {
        return response()->json(['player_id' => $request->session()->get('player_id')]);
    }

    public function delete_session(Request $request)
    {
        $request->session()->forget('player_id');
        return response()->json(['message' => 'session deleted']);
    }
    
    public function check_cellindex($cellindex)
    {
        $return_data = true;
        if($cellindex < 0 || $cellindex > 24)
        {
            $return_data = false;
        }
        return $return_data;
    }

    public function match(Request $request)
    {
        $player_obj = new OysterPlayer();
        $room_obj = new OysterRoom();

        $player_id = $request->session()->get('player_id');

        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが0(待機、ゲーム中ではない)の場合
        if($player->status == 0){
            // ルームのステータスが1(待機中)のものを取得
            $room = $room_obj->search_status(1);
            // 待機ルームが存在する場合
            if($room){
                // ルームにプレイヤーを追加
                $room_obj->update_player($room->id, $player_id);
                // プレイヤーのステータスを1(待機中)に変更
                $player_obj->update_status($player_id, 1);
                // ゲームの準備を行う
                $game_id = OysterController::preparation($room->id);
                // マッチングイベント発行
                event(new OysterPreparation($room->id));
                // 準備画面を返す
                return response()->json(['game_id' => $game_id]);
                // return view('preparation', ['game_id' => $game_id]);
            }
            // ルームのステータスが0(空き)のものを取得
            $room = $room_obj->search_status(0);
            // 空きルームが存在する場合
            if($room)
            {
                // ルームにプレイヤーを追加
                $room_obj->update_player($room->id, $player_id);
                // プレイヤーのステータスを1(待機中)に変更
                $player_obj->update_status($player_id, 1);
                // 待機画面とroom_idを返す
                return response()->json(['room_id' => $room->id]);
                // return view('standby', ['room_id' => $room->id]);
            }
            // ルームが存在しない場合ルームを作成する処理を書く
            return response()->json(['player_id' => $player_id]);
        }
        // プレイヤーのステータスが1(待機中)の場合
        elseif($player->status === 1)
        {
            // プレイヤーが参加しているルームを取得
            $room = $room_obj->search_player($player_id);
            // 待機画面とroom_idを返す
            return response()->json(['room_id' => $room->id]);
            // return view('standby', ['room_id' => $room->id]);
        }
        elseif($player->status === 2)
        {
            $game_obj = new OysterGame();
            $game = $game_obj->player_game($player_id);
            // 準備画面を返す処理を書く
            return response()->json(['game_id' => $game->id]);
            // return view('preparation', ['game_id' => $game->id]);
        }
    }

    public function preparation($room_id)
    {
        $oyster_game = new OysterGame();
        $oyster_room = new OysterRoom();
        $oyster_player = new OysterPlayer();

        // ルームの情報を取得
        $room = $oyster_room->where('id', $room_id)->first();
        // ゲームの作成
        $game_id = $oyster_game->create_game($room->player1, $room->player2);
        // ルームを初期化する
        $oyster_room->reset_room($room_id);
        // プレイヤーのステータスを2(準備中)に変更
        $oyster_player->update_status($room->player1, 2);
        $oyster_player->update_status($room->player2, 2);
        // ゲームIDを返す
        return $game_id;
    }

    public function start_game(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();

        $player_id = $request->session()->get('player_id');
        $game_id = $request->input('game_id');
        $board = $request->input('board');
        // 正規表現でボードの形式をチェック
        if(!preg_match('/^[0-2]{24}$/', $board))
        {
            return response()->json(['message' => 'error']);
        }
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // ゲームの情報を取得
        $game = $game_obj->get_game($game_id);
        // DBに保存されているプレイヤーのボード情報を取得
        $player_board = $game->player1 === $player_id ? $game->player1_board : $game->player2_board;

        // プレイヤーのステータスが2(準備中)かつゲームのステータスが0(準備中)かつプレイヤーのボードが初期状態の場合
        if($player->status === 2 && $game->status === 0 && $player_board === "000000000000000000000000")
        {
            $first = OysterController::first($game_id, $player_id);
            // ボードの状態が正しいかどうかを確認
            if(OysterController::check_board($board, $first))
            {
                // ボードを更新
                $game_obj->update_board($game_id, $player_id, $board);
                // ゲームのステータスを1(片方のプレイヤーが準備完了)に変更
                $game_obj->update_status($game_id, 1);
                return response()->json(['message' => 'ready']);
            }
            else
            {
                return response()->json(['message' => 'error']);
            }
        }
        // プレイヤーのステータスが2(準備中)かつゲームのステータスが1(片方のプレイヤーが準備完了)かつプレイヤーの状態が初期状態の場合
        else if($player->status === 2 && $game->status === 1 && $player_board == "000000000000000000000000")
        {
            $first = OysterController::first($game_id, $player_id);
            // ボードの状態が正しいかどうかを確認
            if(OysterController::check_board($board, $first))
            {
                // ボードを更新
                $game_obj->update_board($game_id, $player_id, $board);
                // ゲームのステータスを2(ゲーム中)に変更
                $game_obj->update_status($game_id, 2);
                // プレイヤーのステータスを3(ゲーム中)に変更
                $player_obj->update_status($game->player1, 3);
                $player_obj->update_status($game->player2, 3);
                // ゲーム開始イベントを発行
                event(new OysterStart($game_id));
                return response()->json(['message' => 'start']);
            }
            else
            {
                return response()->json(['message' => 'error']);
            }
        }
    }

    // 動きと勝利判定ターン終了イベントの発行を行う
    public function move(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();
        $result_obj = new OysterResult();

        $player_id = $request->session()->get('player_id');
        $cellIndex = $request->cellIndex;
        $direction = $request->direction;
        // ボードの初期化
        $player_board = "";
        $enemy_board = "";
        // 勝利判定する0なら特になし1なら勝利2なら敗北
        $win_status = 0;
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが3(ゲーム中)の場合
        if($player->status === 3)
        {
            // プレイヤーが参加しているゲームを取得
            $game = $game_obj->player_game($player_id);
            // プレイヤー1の場合
            if($game->player1 == $player_id)
            {
                $player_board = $game->player1_board;
                $enemy_board = $game->player2_board;
            }
            // プレイヤー2の場合
            else if($game->player2 == $player_id)
            {
                $player_board = $game->player2_board;
                $enemy_board = $game->player1_board;
            }
            // 先攻なら1後攻なら0を返す
            $first = OysterController::first($game->id, $player_id) ? 1 : 0;

            // ターン数が奇数なら先攻の番、偶数なら後攻の番
            if($game->turn % 2 === $first)
            {
                // 自分のボード情報が正しいかチェックする
                $player_board = OysterController::change_board($player_board, $cellIndex, $direction);
                if($player_board)
                {
                    for($i = 0; $i < 24; $i++)
                    {
                        // 自分のオイスターと相手のオイスターが重なっていれば相手のオイスターを0にして削除する
                        if(($player_board[$i] == "1" || $player_board[$i] == "2") && ($enemy_board[$i] == "1" || $enemy_board[$i] == "2"))
                        {
                            $enemy_board[$i] = "0";
                        }
                    }
                    
                    // 最左と最右を決定
                    if($first === 1)
                    {
                        $most_left = 0;
                        $most_right = 3;
                        $win_status = OysterController::win_controll($player_board, $enemy_board, $most_left, $most_right);
                    }
                    else if($first === 0)
                    {
                        $most_left = 20;
                        $most_right = 23;
                        $win_status = OysterController::win_controll($player_board, $enemy_board, $most_left, $most_right);
                    }
                    switch($win_status)
                    {
                        case 0:
                            $turn = $game->turn + 1;
                            // ターン数を増やして保存
                            $game_obj->update_info($player_id, $game->id, $player_board, $enemy_board, $turn);
                            // ターン終了イベントを発行
                            event(new OysterTurnEnd($game->id));
                            return response()->json(['message' => 'ok']);
                        case 1:
                            $winner = 0;
                            if($game->player1 === $player_id)
                            {
                                // 王冠ありオイスターをすべて取得したplayer1の勝利
                                $winner = 1;
                            }
                            else if($game->player2 === $player_id)
                            {
                                // 王冠ありオイスターをすべて取得したplayer2の勝利
                                $winner = 2;
                            }
                            // 勝利なら情報をresultに保存
                            $result_obj->create_result($game->id, $game->player1, $game->player2, $winner);
                            // ゲーム情報は削除する
                            $game_obj->delete_game($game->id);
                            // playerのステータスを4(勝敗決定にする)
                            $player_obj->update_status($game->player1, 4);
                            $player_obj->update_status($game->player2, 4);
                            // 勝敗決定イベントの発行
                            event(new OysterEndGame($game->id));
                            return response()->json(['message' => 'ok']);
                        case 2:
                            $winner = 0;
                            if($game->player1 === $player_id)
                            {
                                // 王冠なしオイスターをすべて取得したplayer1の敗北
                                $winner = 4;
                            }
                            else if($game->player2 === $player_id)
                            {
                                // 王冠なしオイスターをすべて取得したplayer2の敗北
                                $winner = 3;
                            }
                            // 敗北なら情報をresultに保存
                            $result_obj->create_result($game->id, $game->player1, $game->player2, $winner);
                            // ゲーム情報は削除する
                            $game_obj->delete_game($game->id);
                            // playerのステータスを4(勝敗決定にする)
                            $player_obj->update_status($game->player1, 4);
                            $player_obj->update_status($game->player2, 4);
                            // 勝敗決定イベントの発行
                            event(new OysterEndGame($game->id));
                            return response()->json(['message' => 'ok']);
                        case 3:
                            $winner = 0;
                            if($game->player1 === $player_id)
                            {
                                // 両端を取得したplayer1の勝利
                                $winner = 5;
                            }
                            else if($game->player2 === $player_id)
                            {
                                // 両端を取得したplayer2の勝利
                                $winner = 6;
                            }
                            // 敗北なら情報をresultに保存
                            $result_obj->create_result($game->id, $game->player1, $game->player2, $winner);
                            // ゲーム情報は削除する
                            $game_obj->delete_game($game->id);
                            // playerのステータスを4(勝敗決定にする)
                            $player_obj->update_status($game->player1, 4);
                            $player_obj->update_status($game->player2, 4);
                            // 勝敗決定イベントの発行
                            event(new OysterEndGame($game->id));
                            return response()->json(['message' => 'ok']);
                    }
                }
                else
                {
                    return response()->json(['message' => 'your board is invalid']);
                }
            }
            else
            {
                return response()->json(['message' => 'not your turn']);
            }

        }
        else
        {
            // 想定した状態でなければerrorを返す
            return view('error');
        }
    }
    // 自分のボード情報が適切なら変更して返す、正しくなければfalseを返す
    public function change_board($board, $index, $direction)
    {
        // ボードが空の場合、またはインデックスが整数でない場合はfalseを返す
        if ($board === "" || !is_int($index)) {
            return false;
        }

        // インデックスチェック
        if (!OysterController::check_cellindex($index)) {
            return false;
        }

        $my_oyster = $board[$index]; // 自分のオイスターを取得

        // 自分のオイスターが存在しない場合はfalseを返す
        if ($my_oyster !== "1" && $my_oyster !== "2") {
            return false;
        }

        // 方向ごとの処理
        switch ($direction) {
            case "ArrowUp":
                if ($index < 4 || $board[$index - 4] !== "0") {
                    return false; // 移動先が盤面外または空でない場合
                }
                $board[$index - 4] = $my_oyster;
                break;

            case "ArrowDown":
                if ($index >= 20 || $board[$index + 4] !== "0") {
                    return false; // 移動先が盤面外または空でない場合
                }
                $board[$index + 4] = $my_oyster;
                break;

            case "ArrowLeft":
                if ($index % 4 === 0 || $board[$index - 1] !== "0") {
                    return false; // 左端または移動先が空でない場合
                }
                $board[$index - 1] = $my_oyster;
                break;

            case "ArrowRight":
                if ($index % 4 === 3 || $board[$index + 1] !== "0") {
                    return false; // 右端または移動先が空でない場合
                }
                $board[$index + 1] = $my_oyster;
                break;

            default:
                return false; // 無効な方向の場合
        }

        // 元の位置を空に設定
        $board[$index] = "0";

        return $board; // 修正されたボードを返す
    }

    // 勝利判定関数(勝利なら1敗北なら2を返すどちらでもなければ0を返す)
    public function win_controll($board, $enemy_board, $most_left, $most_right)
    {
        // 相手の王冠ありオイスターをすべて取得した勝利
        if(substr_count($enemy_board, "1") === 0)
        {
            return 1;
        }
        // 相手の王冠なしオイスターをすべて取得した敗北
        if(substr_count($enemy_board, "2") === 0)
        {
            return 2;
        }
        // 両端にオイスターがある場合勝利
        if(($board[$most_left] == "1" || $board[$most_left] == "2") && ($board[$most_right] == "1" || $board[$most_right] == "2"))
        {
            return 3;
        }
        return 0;
    }
    // 情報を返す関数
    public function show_info(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();

        $player_id = $request->session()->get('player_id');
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが3(ゲーム中)の場合
        if($player->status === 3)
        {
            // プレイヤーが参加しているゲームを取得
            $game = $game_obj->player_game($player_id);
            // プレイヤー1の場合
            if($game->player1 == $player_id)
            {
                $player_board = $game->player1_board;
                // プレイヤー2の駒情報を結合して返す
                for ($i = 0; $i < 24; $i++)
                {
                    if ($game->player2_board[$i] == 1 || $game->player2_board[$i] == 2)
                    {
                        // 駒の種類が特定されないように3として返す
                        $player_board[$i] = "3";
                    }
                }
            }
            // プレイヤー2の場合
            else if($game->player2 == $player_id)
            {
                $player_board = $game->player2_board;
                // プレイヤー1の駒情報を結合して返す
                for ($i = 0; $i < 24; $i++)
                {
                    if ($game->player1_board[$i] == 1 || $game->player1_board[$i] == 2)
                    {
                        // 駒の種類が特定されないように3として返す
                        $player_board[$i] = "3";
                    }
                }
            }
            // ゲームの情報を返す
            return response()->json(['message' => 'success', 'board' => $player_board, 'turn' => $game->turn]);
        }
        return response()->json(['message' => 'not ready']);
    }

    public function check_standby(Request $request)
    {
        $player_obj = new OysterPlayer();
        $room_obj = new OysterRoom();

        $player_id = $request->session()->get('player_id');
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが1(待機中)の場合
        if($player->status === 1)
        {
            // プレイヤーが参加しているルームを取得
            $room = $room_obj->search_player($player_id);
            // 待機画面とroom_idを返す
            return response()->json(['room_id' => $room->id]);
            // return view('standby', ['room_id' => $room->id]);
        }
        return response()->json(['message' => 'not ready']);
    }

    public function check_preparation(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();

        $player_id = $request->session()->get('player_id');
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが2(準備中)の場合
        if($player->status === 2)
        {
            $game = $game_obj->player_game($player_id);
            // 準備画面とgame_idを返す
            return response()->json(['game_id' => $game->id]);
            // return view('preparation', ['game_id' => $game->id]);
        }
        return response()->json(['message' => 'not ready']);
    }

    public function check_game(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();

        $player_id = $request->session()->get('player_id');
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // プレイヤーのステータスが3(ゲーム中)の場合
        if($player->status === 3)
        {
            $game = $game_obj->player_game($player_id);
            // ゲーム画面とgame_idを返す
            return response()->json(['game_id' => $game->id]);
            // return view('game', ['game_id' => $game->id]);
        }
        return response()->json(['message' => 'not ready']);
    }

    public function check_board($board, $first)
    {
        $king = "1";
        $normal = "2";
        $empty = "0";
        $king_count = 0;
        $normal_count = 0;
        $empty_count = 0;
        // ボードの状態を確認
        if(!preg_match('/^[0-2]{24}$/', $board))
        {
            return false;
        }

        // 先攻の場合
        if($first)
        {
            for($i = 0; $i < 24; $i++)
            {
                if($i === 17 || $i === 18 || $i === 21 || $i === 22)
                {
                    if($board[$i] === $king)
                    {
                        $king_count++;
                    }
                    else if($board[$i] === $normal)
                    {
                        $normal_count++;
                    }
                    else if($board[$i] === $empty)
                    {
                        return false;
                    }
                }
                else if($board[$i] == $empty)
                {
                    $empty_count++;
                }
            }
        }
        // 後攻の場合
        else if(!$first)
        {
            for($i = 0; $i < 24; $i++)
            {   
                if($i === 1 || $i === 2 || $i === 5 || $i === 6)
                {
                    if($board[$i] === $king)
                    {
                        $king_count++;
                    }
                    else if($board[$i] === $normal)
                    {
                        $normal_count++;
                    }
                    else if($board[$i] === $empty)
                    {
                        return false;
                    }
                }
                else if($board[$i] == $empty)
                {
                    $empty_count++;
                }
            }
        }
        // オイスターの数が正しいかどうかを確認
        if($king_count === 2 && $normal_count === 2 && $empty_count === 20)
        {
            return true;
        }
        return false;
    }

    public function first($game_id, $player_id)
    {
        $game_obj = new OysterGame();
        $player_obj = new OysterPlayer();

        // ゲームの情報を取得
        $game = $game_obj->get_game($game_id);
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // 先攻プレイヤーかどうかを判定
        if($game->first_turn == 0)
        {
            // プレイヤー1の場合
            if($game->player1 == $player_id)
            {
                return true;
            }
            return false;
        }
        // 後攻プレイヤーかどうかを判定
        else if($game->first_turn == 1)
        {
            // プレイヤー2の場合
            if($game->player2 == $player_id)
            {
                return true;
            }
            return false;
        }
        // それ以外の場合
    }

    public function first_turn(Request $request)
    {
        $player_obj = new OysterPlayer();
        $game_obj = new OysterGame();

        $player_id = $request->session()->get('player_id');
        // プレイヤーの情報を取得
        $player = $player_obj->get_player($player_id);
        // ゲームの情報を取得
        $game = $game_obj->player_game($player_id);
        // $return_data = OysterController::first($game->id, $request->session()->get('player_id'));
        $return_data = OysterController::first($game->id, $request->session()->get('player_id')) ? 1 : 0;
        return response()->json(['first' => $return_data]);
    }
    // result画面を返す
    public function show_result(Request $request, $game_id)
    {
        $player_obj = new OysterPlayer;
        $result_obj = new OysterResult;

        // セッションからプレイヤーIDを取得
        $player_id = $request->session()->get('player_id');
        if (!$player_id) {
            return response()->json(['message' => 'Player ID not found in session'], 400);
        }

        // プレイヤー情報を取得
        $player = $player_obj->get_player($player_id);
        if (!$player || $player->status !== 4) {
            return response()->json(['message' => 'Invalid player status or not waiting for results'], 400);
        }

        // ゲーム結果を取得
        $result = $result_obj->game_result($game_id);
        if (!$result) {
            return response()->json(['message' => 'Game result not found'], 404);
        }

        // プレイヤーがplayer1またはplayer2であるかを確認
        if ($result->player1 === $player_id) {
            // プレイヤーのステータスを0に戻す
            $player_obj->update_status($player_id, 0);
            // 余りを求める奇数ならplayer1の勝利、偶数ならplayer2の勝利
            $rem = $result->winner % 2;
            // 商から勝利の理由を求める
            $win_result = ($result->winner + $rem) / 2;
            // return $result->winner === 1 ? view('win') : view('lose');
            // 余りが1なら0(勝利)を余りが0なら1(敗北)を返す、win_resultには勝敗の理由を返す
            return response()->json(['winner' => $rem === 1 ? 0 : 1, 'win_result' => $win_result]);
        } elseif ($result->player2 === $player_id) {
            $player_obj->update_status($player_id, 0);
            // return $result->winner === 2 ? view('win') : view('lose');
            // 余りが0なら0(勝利)を余りが1なら1(敗北)を返す、win_resultには勝敗の理由を返す
            return response()->json(['winner' => $rem === 0 ? 0 : 1, 'win_result' => $win_result]);
        }

        // どちらのプレイヤーにも該当しない場合
        return response()->json(['message' => 'Player not part of this game'], 403);
    }
    public function return_player_status(Request $request)
    {
        $player_obj = new OysterPlayer();
        $player_id = $request->session()->get('player_id');
        $player = $player_obj->get_player($player_id);
        return response()->json(['status' => $player->status]);
    }
}