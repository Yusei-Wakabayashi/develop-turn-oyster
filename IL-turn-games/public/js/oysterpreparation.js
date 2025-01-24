function requestOysterPreparation(game_id) {
    var board = document.getElementById("board").value;
    const controller = new AbortController();
    const signal = controller.signal;
    fetch('/oyster/start', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ "board": board, "game_id": game_id}),
        signal: signal // AbortSignal を設定
    })
    .then(response => response.json()) // サーバーからのレスポンスをJSONで受け取る
    .then(data => {
        console.log(data);
        if (data['message'] == 'error') {
            console.log("Oyster preparation is failed.");
            // エラーメッセージを表示
            alert("Oyster preparation is failed.");
            // もう一度入力を促すメッセージを表示
            document.getElementById("message").innerHTML = "Please enter the board again.";
        }
        else if (data['message'] == 'ready') {
            console.log("Oyster preparation is successful.");
            // 待機を促すメッセージを表示
            alert("Oyster preparation is successful. Please wait for the opponent.");
            // 入力を制限する処理を書く
            document.getElementById("board").readOnly = true;
            // ボタンを非活性にする
            document.getElementById("sendbutton").disabled = true;
        }
        else if (data['message'] == 'start') {
            console.log("Oyster preparation is successful.");
            // 待機を促すメッセージを表示
            alert("Oyster preparation is successful. Please wait for the opponent.");
            // 入力を制限する処理を書く
            document.getElementById("board").readOnly = true;
            // ボタンを非活性にする
            document.getElementById("sendbutton").disabled = true;
        }
        else {
            console.log("Oyster preparation is failed.");
            // エラーメッセージを表示
            alert("Oyster failed.");
            // もう一度入力を促すメッセージを表示
            document.getElementById("message").innerHTML = "Please enter the board again."; 
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') {
            console.log('Fetch request was aborted');
        } else {
            console.log('Fetch error:', error);
        }
    });
}

function check_oysterpreparation(game_id) {
    var board = document.getElementById("board").value;
    // ボードが空の場合
    if((board == "") || (board == null)) {
        alert("Please enter the board.");
        return false;
    }
    // 正規表現でボードの形式をチェック
    var reg = new RegExp("^[0-2]{24}$");
    if (!reg.test(board)) {
        alert("Please enter the board in the correct format.");
        return false;
    }
    console.log(game_id);
    fetch(`/oyster/first`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json()) // サーバーからのレスポンスをJSONで受け取る
    .then(data => {
        if (data['first']) {
            // 適切な位置にオイスターがあるかどうかをチェック
            var kingoyster = 1;
            var nomaloyster = 2;
            var kingcount = 0;
            var nomalcount = 0;
            var count = 0;

            for (var i = 0; i < 24; i++) {
                if (i == 17 || i == 18 || i == 21 || i == 22) {
                    if (board[i] == kingoyster) {
                        kingcount++;
                    }
                    else if (board[i] == nomaloyster) {
                        nomalcount++;
                    }
                    else{
                        alert("Oysters are not in the correct position.");
                        return false;
                    }
                }
                else if (board[i] == 0) {
                    count++;
                }
            }
            // オイスターの数が正しいかどうかをチェック
            if (kingcount == 2 && nomalcount == 2 && count == 20) {
                requestOysterPreparation(game_id);
                alert("Oysters are in the correct position.");
                return true;
            }
            else {
                alert("Oysters are not in the correct position.");
                return false;
            }
        }
        else {
            // 適切な位置にオイスターがあるかどうかをチェック
            var kingoyster = 1;
            var nomaloyster = 2;
            var kingcount = 0;
            var nomalcount = 0;
            var count = 0;

            for (var i = 0; i < 24; i++) {
                if (i == 1 || i == 2 || i == 5 || i == 6) {
                    if (board[i] == kingoyster) {
                        kingcount++;
                    }
                    else if (board[i] == nomaloyster) {
                        nomalcount++;
                    }
                    else{
                        alert("Oysters are not in the correct position.");
                        return false;
                    }
                }
                else if (board[i] == 0) {
                    count++;
                }
            }
            // オイスターの数が正しいかどうかをチェック
            if (kingcount == 2 && nomalcount == 2 && count == 20) {
                requestOysterPreparation(game_id);
                alert("Oysters are in the correct position.");
                return true;
            }
            else {
                alert("Oysters are not in the correct position.");
                return false;
            }
        }
    })
    return true;
}