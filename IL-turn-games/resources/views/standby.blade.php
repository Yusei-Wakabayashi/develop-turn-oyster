<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>standby</title>
</head>
<body>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        async function get_room_id() {
            // fetch を使って room_id を取得する
            try {
                const response = await fetch(`/oyster/id/room`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();

                if (data['room_id']) {
                    return data['room_id'];
                } else {
                    console.error("room_id が取得できませんでした");
                    return null;
                }
            } catch (error) {
                console.error("Error fetching room ID:", error);
                return null;
            }
        }

        async function initialize() {
            const room_id = await get_room_id(); // ここで await を適用

            if (!room_id) {
                console.error("room_id を取得できませんでした");
                return;
            }

            console.log("取得した room_id:", room_id);

            Pusher.logToConsole = true;
            const pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
                cluster: "{{ config('const.pusher.cluster') }}"
            });

            const channel = pusher.subscribe('room.' + room_id);
            channel.bind('preparation', function(data) {
                document.getElementById("match_text").textContent = "PlayerMatched!";
                window.location.href = "{{ url('/oyster/preparation') }}";
            });
        }

        // `DOMContentLoaded` イベントを待ってから実行
        document.addEventListener("DOMContentLoaded", () => {
            initialize();
        });
    </script>
    <h1 id="match_text">マッチング中</h1>
</body>
</html>