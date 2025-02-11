<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OysterGame</title>
</head>
<body>
    <h1>game start</h1>
    <table class="board">
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <div id="turn"></div>
    <div id="change"></div>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="{{asset('js/oystergame.js')}}"></script>
    <script>
        async function get_game_id() {
            try {
                const response = await fetch(`/oyster/id/game`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await response.json();
                console.log("取得した game_id:", data['game_id']);
                return data['game_id'] || null;
            } catch (error) {
                console.error("Error fetching game ID:", error);
                return null;
            }
        }

        async function initializeGame() {
            const controller = new AbortController();
            const game_id = await get_game_id();
            
            if (!game_id) {
                console.error("ゲームIDが取得できませんでした");
                return;
            }

            console.log("取得した game_id:", game_id);

            Pusher.logToConsole = true; // ログを有効化

            var pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
                cluster: "{{ config('const.pusher.cluster') }}",
                forceTLS: true
            });

            var channel = pusher.subscribe(`game.${game_id}`);
            console.log("Subscribed to channel:", channel.name);

            channel.bind('game_end', function(data) {
                console.log("game_end イベント受信:", data);
                if (typeof controller !== "undefined") {
                    controller.abort();
                }
                window.location.href = `{{ url('/oyster/image/result') }}`;
            });

            channel.bind('turn_end', function(data) {
                console.log("turn_end イベント受信:", data);
                game_info();
            });

            game_info();
        }

        document.addEventListener("DOMContentLoaded", initializeGame);
    </script>
</body>
</html>