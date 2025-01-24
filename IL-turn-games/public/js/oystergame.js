// 要素を指定
const tableCells = document.querySelectorAll('.board td');
console.log(tableCells);
async function get_game_info() {
    // サーバーからゲーム情報を取得
    const response = await fetch('/oyster/info', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    });
    const data = await response.json(); // サーバーからのレスポンスをJSONで受け取る
    console.log(data);
    // サーバーからのレスポンスが成功ならばゲーム情報を返す
    if (data['message'] === 'success') {
        console.log('success');
        return data;
    }
    // サーバーからのレスポンスが失敗ならばfalseを返す
    else {
        console.log('not success');
        return false;
    }
}
// 先攻後攻のチェック
async function checkfirst() {
    // サーバーから先攻後攻の情報を取得
    const response = await fetch('/oyster/first', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    });
    const data = await response.json(); // サーバーからのレスポンスをJSONで受け取る
    console.log(data);

    // 先攻か後攻を判定
    return data['first'] ? 1 : 0;
}
// ゲーム情報の取得
async function game_info() {
    try {
        // 非同期処理を待つ
        const first = await checkfirst();
        console.log(first);
        // 先攻後攻の情報が取得できなかった場合
        if (first === null) {
            console.log('error');
            return;
        }
        // 非同期処理を待つ
        const game_data = await get_game_info();
        console.log(game_data);
        // ゲーム情報を取得できればボードを更新
        if (game_data) {
            update_board(game_data['board']);
            // 自分が先行であれば1、後攻であれば0がfirstに格納されている
            var turnMessage = "default";
            // ターンがの余りがfirstと一致すれば自分のターン
            if (game_data['turn'] % 2 === first) {
                // 自分のターンであれば操作を行えるようにする
                enableInput();
                turnMessage = "Your turn";
            }
            // 一致しなければ相手のターン
            else{
                // 相手のターンであれば操作を行えないようにする
                disableInput();
                turnMessage = "Opponent's turn";
            }
            
            document.getElementById('turn').textContent = turnMessage;
        }
        // ゲーム情報を取得できなければエラーを表示
        else {
            console.error('Failed to fetch game data');
        }
    }
    // エラーが発生した場合
    catch (error) {
        console.error('An error occurred:', error);
    }
}

function update_board(board) {
    // サーバーから送られたボード状態をもとにクライアントのボードを更新
    for (let i = 0; i < board.length; i++) {
        const cell = tableCells[i]; // 対応する<td>要素を取得
        let content;
        // ボードの状態に応じて表示する文字を変える
        switch (board[i]) {
            // 0: 空白
            case "0":
                content = '■';
                break;
            // 1: kingoyster
            case "1":
                content = '○';
                break;
            // 2: normaloyster
            case "2":
                content = '●';
                break;
            // 3: enemyoyster
            case "3":
                content = '▲';
                break;
            default:
                content = ''; // 未知の値の場合
                break;
        }
        if (cell) {
            cell.textContent = content;
        }
    }
}
// td要素がクリックされたら
var cellIndex;
tableCells.forEach(cell => {
    cell.addEventListener('click', () => {
        // boardの何番目がクリックされたかを取得
        cellIndex = Array.prototype.indexOf.call(tableCells, cell);
        console.log(cellIndex);
    });
});

// 動かしたい方向を矢印キーで取得
document.addEventListener('keydown', (event) => {
    // クリックされた状態かつ矢印キーが押された状態であれば
    if (cellIndex !== undefined) {
        // 盤面の要素番号と矢印キーの情報を取得
        move_piece(cellIndex, event.key);
        console.log(event.key);
    }
});

// 要素数チェック関数
function check_cellIndex(cellIndex) {
    // 要素番号は0~23の想定
    if (cellIndex < 0 || cellIndex > 23) {
        console.log('error');
        return false;
    }
    return true;
}
// クリックされた状態かつ矢印キーが押された状態であれば
// 盤面の要素番号と矢印キーの情報を取得し想定した盤面であれば変更する関数
function move_piece(cellIndex, direction) {
    var input_check = 0;
    // クリックされた要素の中身を取得
    var my_piece = tableCells[cellIndex].textContent;

    cellIndex = parseInt(cellIndex);
    // 要素番号は0~23の想定
    if (!check_cellIndex(cellIndex)) {
        console.log('cellIndex error');
        return false;
    }
    // 選択された要素が1,2に対応する(自分のオイスター)であれば動かせるように
    if (my_piece === '○' || my_piece === '●') {
        console.log('my oyster ok');
        input_check += 1;
    }
    // 移動先が0,3(空白、相手のオイスター)であれば動かせるように
    switch (direction) {
    case 'ArrowUp':
        if (!check_cellIndex(cellIndex - 4)) {
            console.log('direction over');
            return false;
        }
        // 上なので-4して■(3)か▲(0)であれば動かせるように
        if (tableCells[cellIndex - 4].textContent === '■' || tableCells[cellIndex - 4].textContent === '▲') {
            console.log('direction oyster ok');
            input_check += 1;
        }
        // 4で割った結果が1未満なら上には動かせない
        if (cellIndex / 4 < 1) {
            console.log('direction index error');
            input_check -= 1;
        }
        break;
    case 'ArrowDown':
        if (!check_cellIndex(cellIndex + 4)) {
            console.log('direction over');
            return false;
        }
        // 下なので+4して■(3)か▲(0)であれば動かせるように
        if (tableCells[cellIndex + 4].textContent === '■' || tableCells[cellIndex + 4].textContent === '▲') {
            console.log('direction oyster ok');
            input_check += 1;
        }
        // 4で割った結果が5以上なら下には動かせない
        if (cellIndex / 4 >= 5) {
            console.log('direction index error');
            input_check -= 1;
        }
        break;
    case 'ArrowLeft':
        if (!check_cellIndex(cellIndex - 1)) {
            console.log('direction over');
            return false;
        }
        // 左なので-1して■(3)か▲(0)であれば動かせるように
        if (tableCells[cellIndex - 1].textContent === '■' || tableCells[cellIndex - 1].textContent === '▲') {
            console.log('direction oyster ok');
            input_check += 1;
        }
        // 4で割った余りが0なら左には動かせない
        if (cellIndex % 4 === 0) {
            console.log('direction index error');
            input_check -= 1;
        }
        break;
    case 'ArrowRight':
        if (!check_cellIndex(cellIndex + 1)) {
            console.log('direction over');
            return false;
        }
        // 右なので+1して■(3)か▲(0)であれば動かせるように
        if (tableCells[cellIndex + 1].textContent === '■' || tableCells[cellIndex + 1].textContent === '▲') {
            console.log('direction oyster ok');
            input_check += 1;
        }
        // 4で割った余りが3なら右には動かせない
        if (cellIndex % 4 === 3) {
            console.log('direction index error');
            input_check -= 1;
        }
        break;
    default:
        console.log('input error');
        break;
    }
    // 全ての条件を満たしていれば盤面を変更
    if (input_check === 2) {
        // 自分の元の居場所には■を表示
        tableCells[cellIndex].textContent = '■';
        // 移動先に自分のオイスターを表示
        switch (direction) {
        case 'ArrowUp':
            tableCells[cellIndex - 4].textContent = my_piece;
            break;
        case 'ArrowDown':
            tableCells[cellIndex + 4].textContent = my_piece;
            break;
        case 'ArrowLeft':
            tableCells[cellIndex - 1].textContent = my_piece;
            break;
        case 'ArrowRight':
            tableCells[cellIndex + 1].textContent = my_piece;
            break;
        default:
            console.log('input error');
            break;
        }
        // 自分の番になるまで操作を行えないようにする
        disableInput();
        // ボード情報をサーバーに送信する関数
        sendMoveRequest(cellIndex, direction);
    }
    else
    {
        console.log('your change is invalid');
        alert('plese change your board');
    }
    console.log(cellIndex, direction);
}


// ボード情報をサーバーに送信する関数
function sendMoveRequest(cellIndex, direction) {
    const controller = new AbortController();
    const signal = controller.signal;
    fetch('/oyster/move', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
            "cellIndex": cellIndex,
            "direction": direction,
        }),
        signal: signal // AbortSignal を設定
    })
    .then(response => response.json())
    .then(data => {
        if (data['message'] === 'ok') {
            console.log('Move successful');
            update_board(data.board); // サーバーからの新しい盤面を反映
        } else {
            console.log('Move failed:', data['message']);
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
// 自分の番に帰ってくるまで操作を行えないようにする関数
function disableInput() {
    tableCells.forEach(cell => {
        cell.style.pointerEvents = 'none';
    });
}
// 自分の番になったら操作を行えるようにする関数
function enableInput() {
    tableCells.forEach(cell => {
        cell.style.pointerEvents = 'auto';
    });
}
