<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>preparation</title>
</head>
<body>
    <h1 id="match_text">preparation</h1>
    <!-- 選択可能なアイテム -->
    <div>
        <span id="select-o" class="selectable" data-type="1">○ rest<span id="count-o">2</span></span>
        <span id="select-x" class="selectable" data-type="2">● rest<span id="count-x">2</span></span>
        <button id="clear-selection">clear select</button> <!-- 選択解除ボタンを追加 -->
    </div>
    <!-- <textarea name="" id="board"></textarea> -->
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
    <span id="explanation">Please place it in the green position</span>
    <button id="sendbutton">decision</button>
    <div id="message"></div>
    <span id="randomquestion">Do you want to set it randomly?</span>
    <button id="randombutton">oyster random set</button>
    <div id="oysterexplanation">
        ○ is kingoyster
        ● is normaloyster
    </div>
    <style>
        .selected {
            background-color: yellow; /* 選択中のアイテムを強調 */
        }
        .possible {
            background-color: lightgreen; /* 配置可能な位置を強調 */
        }
        #explanation
        {
            color: blue;
        }
        #randomquestion
        {
            color: green;
        }
    </style>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="{{asset('js/oysterpreparation.js')}}"></script>
    <script>
        // 非同期で先攻後攻情報を取得
        async function get_first_info()
        {
            try{
                const response = await fetch(`/oyster/first`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                });
                const data = await response.json();
                return data['first'];
            }
            catch (error)
            {
                console.error("Error fetching first info:", error);
                return null;
            }
        }
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
            let oyster_board = document.querySelectorAll('.board td');
            for (let i = 0; i < oyster_board.length; i++)
            {
                oyster_board[i].textContent = '■';
            }
            console.log(oyster_board);
            const controller = new AbortController();
            Pusher.logToConsole = true;
            // first info を取得
            const first_info = await get_first_info();
            // 配置可能な位置をわかりやすく表示
            // 先攻なら17,18,21,22の色を変える
            if(first_info == 1)
            {
                oyster_board[17].classList.add("possible");
                oyster_board[18].classList.add("possible");
                oyster_board[21].classList.add("possible");
                oyster_board[22].classList.add("possible");
            }
            // 後攻なら1,2,5,6の色を変える
            else if(first_info == 0)
            {
                oyster_board[1].classList.add("possible");
                oyster_board[2].classList.add("possible");
                oyster_board[5].classList.add("possible");
                oyster_board[6].classList.add("possible");
            }
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
                check_oysterpreparation(game_id, first_info);
            });

            var randombutton = document.getElementById("randombutton");
            randombutton.addEventListener("click", function() {
                random_generate_oyster(first_info);
            });
        }

        // DOM読み込み完了後に `initialize()` を実行
        document.addEventListener("DOMContentLoaded", () => {
            initialize();
        });
        

        // 以下はテスト
        let remaining = { "1": 2, "2": 2 }; // ○と●の残り数
        let selectedType = null; // 選択中のタイプ（"1"=○, "2"=●）

        // ○と●を選択する
        document.querySelectorAll(".selectable").forEach(item => {
            item.addEventListener("click", function () {
                selectedType = this.dataset.type; // 選択中のタイプを設定
                document.querySelectorAll(".selectable").forEach(i => i.classList.remove("selected"));
                this.classList.add("selected");
            });
        });

        // 選択解除ボタン
        document.getElementById("clear-selection").addEventListener("click", function () {
            selectedType = null;
            document.querySelectorAll(".selectable").forEach(i => i.classList.remove("selected"));
        });

        // 盤面のセルをクリックしたときの処理
        document.querySelectorAll(".board td").forEach(cell => {
            cell.addEventListener("click", function () {
                if (!selectedType) return; // 何も選択していなければ処理しない

                let current = this.textContent;

                if (current === "■" && remaining[selectedType] > 0) {
                    // 空白マスに○または●を配置
                    this.textContent = selectedType === "1" ? "○" : "●";
                    remaining[selectedType]--; // 残り数を減らす
                } else if (
                    (current === "○" && selectedType === "1") ||
                    (current === "●" && selectedType === "2")
                ) {
                    // 自分が選択している駒と一致する場合のみ削除
                    this.textContent = "■";
                    remaining[selectedType]++; // 残り数を戻す
                }

                // 残り数を更新
                document.getElementById("count-o").textContent = remaining["1"];
                document.getElementById("count-x").textContent = remaining["2"];
            });
        });
        // 以上でテストコード終了
    </script>
</body>
</html>

</body>
</html>