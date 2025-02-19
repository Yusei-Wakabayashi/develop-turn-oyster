function requestOysterPreparation(game_id, board) {
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
            // boardを制限する処理を書く
            disableInput();
            // ボタンを非活性にする
            let sendbutton = document.getElementById("sendbutton");
            let randombutton = document.getElementById("randombutton");
            sendbutton.disabled = true;
            randombutton.disabled = true;
        }
        else if (data['message'] == 'start') {
            console.log("Oyster preparation is successful.");
            // 待機を促すメッセージを表示
            alert("Oyster preparation is successful. Please wait for the opponent.");
            // boardを制限する処理を書く
            disableInput();
            // ボタンを非活性にする
            let sendbutton = document.getElementById("sendbutton");
            let randombutton = document.getElementById("randombutton");
            sendbutton.disabled = true;
            randombutton.disabled = true;
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

function check_oysterpreparation(game_id, first_info) {
    var oyster_board = document.querySelectorAll('.board td');
    var board = [];
    for (let i = 0; i < oyster_board.length; i++)
    {
        switch(oyster_board[i].innerHTML)
        {
            case '■':
                board.push('0');
                break;
            case '○':
                board.push('1');
                break;
            case '●':
                board.push('2');
                break;
            default:
                console.log('case error');
                break;
        }
    }
    board = board.join('');
    console.log(board);
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
    //1なら先攻
    if (first_info == 1) {
        // 適切な位置にオイスターがあるかどうかをチェック
        var kingoyster = 1;
        var nomaloyster = 2;
        var kingcount = 0;
        var nomalcount = 0;
        var count = 0;
        // 先攻なら17,18,21,22の下側に自分のオイスターが配置されているか確認
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
            requestOysterPreparation(game_id, board);
            alert("Oysters are in the correct position.");
            return true;
        }
        else {
            alert("Oysters are not in the correct position.");
            return false;
        }
    }
    //0なら後攻
    else if(first_info == 0) {
        // 適切な位置にオイスターがあるかどうかをチェック
        var kingoyster = 1;
        var nomaloyster = 2;
        var kingcount = 0;
        var nomalcount = 0;
        var count = 0;
        // 後攻なら1,2,5,6の上側に自分のオイスターが配置されているか確認
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
            requestOysterPreparation(game_id, board);
            alert("Oysters are in the correct position.");
            return true;
        }
        else {
            alert("Oysters are not in the correct position.");
            return false;
        }
    }
    return true;
}

function random_oyster(first_info) {
    let arr = generateRandomArray();
    let oyster_string;

    if (first_info == 1) {
        oyster_string = '0000000000000000' + '0' + String(arr[0]) + String(arr[1]) + '0' + '0' + arr[2] + arr[3] + '0';
    } else if (first_info == 0) {
        oyster_string = '0' + String(arr[0]) + String(arr[1]) + '0' + '0' + arr[2] + arr[3] + '0' + '0000000000000000';
    }

    console.log(oyster_string);
    return oyster_string;
}

function generateRandomArray() {
    let arr = [1, 1, 2, 2]; // 1を2個、2を2個用意

    // Fisher-Yatesアルゴリズムでシャッフル
    for (let i = arr.length - 1; i > 0; i--) {
        let j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]]; // 要素を交換
    }
    return arr;
}

function random_generate_oyster(first_info) {
    let oyster_board = document.querySelectorAll('.board td');
    if (oyster_board) {
        let oyster_string = random_oyster(first_info); // 非同期処理が完了するまで待つ
        for (let i = 0; i < oyster_string.length; i++)
        {
            let content;
            if(oyster_string[i] == "1")
            {
                content = '○';
            }
            else if(oyster_string[i] == "2")
            {
                content = '●';
            }
            else if(oyster_string[i] == "0")
            {
                content = '■';
            }
            oyster_board[i].textContent = content;
        }
        document.getElementById("count-o").textContent = 0;
        document.getElementById("count-x").textContent = 0;
        remaining["1"] = 0;
        remaining["2"] = 0;
    } else {
        console.error("要素 'board' が見つかりません");
    }
}

function disableInput()
{
    let oyster_board = document.querySelectorAll('.board td');
    for (let i = 0; i < oyster_board.length; i++)
    {
        oyster_board[i].style.pointerEvents = 'none';
    }
}