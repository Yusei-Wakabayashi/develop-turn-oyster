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
        // 非同期関数として定義
        async function get_game_id() {
            // fetch を使って game_id を取得する
            try {
                const response = await fetch(`/oyster/id/preparation`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });
                // レスポンスを JSON としてパース
                const data = await response.json();
                console.log(data);
                // game_id があれば返す
                if (data['game_id'])
                {
                    return data['game_id'];
                }
                // game_idがなければエラーを出力
                else
                {
                    console.error("game_id が取得できませんでした");
                    return null;
                }
            }
            catch (error)
            {
                console.error("Error fetching game ID:", error);
                return null;
            }
        }
        // 非同期関数として定義
        async function initialize() {
            const controller = new AbortController();
            Pusher.logToConsole = true;
            // game_id を取得
            const game_id = await get_game_id(); // ここで await を適用
            if (!game_id) {
                console.error("game_id を取得できませんでした");
                return;
            }
            // game_id をコンソールに出力
            console.log("取得した game_id:", game_id);

            var pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
                cluster: "{{ config('const.pusher.cluster') }}"
            });

            var channel = pusher.subscribe(`game.${game_id}`);
            channel.bind('preparation', function(data) {
                document.getElementById("message").textContent = "gamestart!";
                if (typeof controller !== "undefined") {
                    controller.abort();
                }
                window.location.href = "{{ url('/oyster/game') }}";
            });

            var sendbutton = document.getElementById("sendbutton");
            sendbutton.addEventListener("click", function() {
                check_oysterpreparation(game_id);
            });
        }

        // DOM読み込み完了後に `initialize()` を実行
        document.addEventListener("DOMContentLoaded", () => {
            initialize();
        });
    </script>
</body>
</html>

</body>
</html>