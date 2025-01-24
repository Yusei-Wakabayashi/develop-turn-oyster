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
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="{{asset('js/oystergame.js')}}"></script>
    <script>
        const controller = new AbortController();
        var pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
            cluster: "{{ config('const.pusher.cluster') }}"
        });
        // チャンネル名に埋め込まれた変数をJavaScriptで適切に渡す
        var game_id = @json($game_id); // Bladeの変数をJavaScriptに渡す
        var channel = pusher.subscribe(`game.${game_id}`);
        channel.bind('game_end', function(data) {
            if (typeof controller !== "undefined") {
                controller.abort();
            }
            console.log('game_end');
            // ゲーム終了イベント検知で結果取得
            window.location.href = `{{ url('/oyster/result') }}/${game_id}`;
        });
        channel.bind('turn_end', function(data) {
            // ターン終了イベント検知でゲーム情報取得
            console.log('turn_end');
            game_info();
        });
        game_info();
    </script>
</body>
</html>