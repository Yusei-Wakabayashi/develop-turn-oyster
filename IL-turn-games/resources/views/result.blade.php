<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>result_image</title>
</head>
<body>
    <h1 id="result"></h1>
    <div id="cause"></div>
    <div id="kingnum"></div>
    <div id="normalnum"></div>
    <script src="{{asset('js/oysterresult.js')}}"></script>
    <button id="sendbutton">once again</button>
    <script>
        var sendbutton = document.getElementById("sendbutton");
        sendbutton.addEventListener("click", function() {
            sendmatch();
        });
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

        async function get_oyster_number(game_id)
        {
            try {
                const response = await fetch(`/oyster/number/${game_id}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await response.json();
                console.log("取得した king:", data['kingoyster']);
                console.log("取得した normal:", data['normaloyster']);
                //
                document.getElementById("kingnum").textContent = 'you get kingoyster' + data['kingoyster'];
                document.getElementById("normalnum").textContent = 'your get normaloyster' +  data['normaloyster'];

                return data || null;
            } catch (error) {
                console.error("Error:", error);
                return null;
            }
        }

        async function result_info(game_id)
        {
            try {
                const response = await fetch(`/oyster/result/${game_id}`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                });
                const data = await response.json();
                console.log("取得した winner:", data['winner']);
                console.log("取得した win_result:", data['win_result']);
                if(data['winner'] == 0)
                {
                    document.getElementById("result").textContent = "you win";
                }
                else
                {
                    document.getElementById("result").textContent = "you lose";
                }
                switch (data['win_result'])
                {
                    case 0:
                        document.getElementById("cause").textContent = "unexpected error";
                        break;
                    case 1:
                        document.getElementById("cause").textContent = "kingoyster get";
                        break;
                    case 2:
                        document.getElementById("cause").textContent = "normaloyster get";
                        break;
                    case 3:
                        document.getElementById("cause").textContent = "occupation of both ends";
                        break;
                    default:
                        document.getElementById("cause").textContent = "etc...";
                        break;
                }
            } catch (error) {
                console.error("Error fetching result info:", error);
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
            result_info(game_id);
            get_oyster_number(game_id);
        }

        document.addEventListener("DOMContentLoaded", initializeGame);
    </script>
    <form action="{{ url('/oyster/title') }}" method="GET">
        @csrf
        <label for="">Return to title</label>
        <button type="submit" class="btn btn-primary">end</button>
    </form>
</body>
</html>