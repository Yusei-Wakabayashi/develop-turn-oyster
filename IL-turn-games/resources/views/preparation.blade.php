<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>preparation</title>
</head>
<body>
    <h1 id="match_text">準備</h1>
    <textarea name="" id="board"></textarea>
    <button id="sendbutton">決定</button>
    <div id="message"></div>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="{{asset('js/oysterpreparation.js')}}"></script>
    <script>
        const controller = new AbortController();
        Pusher.logToConsole = true;
        var pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
            cluster: "{{ config('const.pusher.cluster') }}"
        });
        // チャンネル名に埋め込まれた変数をJavaScriptで適切に渡す
        var game_id = @json($game_id); // Bladeの変数をJavaScriptに渡す
        var channel = pusher.subscribe(`game.${game_id}`);
        channel.bind('preparation', function(data) {
            document.getElementById("message").textContent = "gamestart!";
            // Fetchなどの進行中リクエストを中断する
            if (typeof controller !== "undefined") {
                controller.abort();
            }
            window.location.href = "{{ url('/oyster/game') }}";
        });
        var sendbutton = document.getElementById("sendbutton");
        sendbutton.addEventListener("click", function() {
            check_oysterpreparation(game_id);
        });
    </script>
</body>
</html>