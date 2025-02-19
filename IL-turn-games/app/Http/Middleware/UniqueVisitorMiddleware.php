<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\OysterPlayer;

class UniqueVisitorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // セッションに一意のIDが存在しない場合、新たに生成
        if (!$request->session()->has('player_id')) {
            $playerId = uniqid('player_', true); // 一意のIDを生成
            $request->session()->put('player_id', $playerId); // セッションに保存
        }
        // セッションからplayer_idを取得
        $player_id = $request->session()->get('player_id');
        $oysterplayer = new OysterPlayer();
        // player_idを基にデータを取得
        $player_num = $oysterplayer->get_player($player_id);
        // データが存在しなければ作成
        if(!$player_num)
        {
            $oysterplayer->create_player($player_id);
        }
        return $next($request);
    }
}
