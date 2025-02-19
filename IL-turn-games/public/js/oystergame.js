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

    // 先攻か後攻を返す
    return data['first'];
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
        // 先攻後攻で相手の両端を緑に自分の両端を赤くする
        var oyster_board = document.querySelectorAll('.board td');
        if(first === 1)
        {
            oyster_board[0].classList.add("enemyboth");
            oyster_board[3].classList.add("enemyboth");
            oyster_board[20].classList.add("myboth");
            oyster_board[23].classList.add("myboth");
        }
        else if(first === 0)
        {
            oyster_board[0].classList.add("myboth");
            oyster_board[3].classList.add("myboth");
            oyster_board[20].classList.add("enemyboth");
            oyster_board[23].classList.add("enemyboth");
        }
        // 非同期処理を待つ
        const game_data = await get_game_info();
        await get_result_info();
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

// 変更履歴の取得
async function get_result_info() {
    // サーバーからゲーム情報を取得
    const response = await fetch('/oyster/change/result', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    });
    const data = await response.json(); // サーバーからのレスポンスをJSONで受け取る
    console.log(data);
    // サーバーからのレスポンスが成功ならばゲーム情報を返す
    var message = "";
    if(data['message'] === 'no change')
    {
        message = "";
    }
    if (data['player'] === 'you') {
        console.log('you get oyster');
        if(data['oyster'] === 1)
        {
            message = "You get kingoyster!";
            console.log('you get kingoyster');
        }
        else if(data['oyster'] === 2)
        {
            message = "You get normaloyster!";
            console.log('you get normaloyster');
        }
    }
    else if (data['player'] === 'enemy') {
        console.log('enemy get oyster');
        if(data['oyster'] === 1)
        {
            message = "Enemy get kingoyster!";
            console.log('enemy get kingoyster');
        }
        else if(data['oyster'] === 2)
        {
            message = "Enemy get normaloyster!";
            console.log('enemy get normaloyster');
        }
    }
    // サーバーからの情報が変更なしならfalseを返す
    else if(data['message'] == 'nochange')
    {
        console.log('nochange');
        return false;
    }
    // サーバーからのレスポンスが失敗ならばfalseを返す
    else {
        console.log('not success');
        return false;
    }
    document.getElementById('change').textContent = message;
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
var cellIndex = null;
tableCells.forEach(cell => {
    cell.addEventListener('click', () => {
        clear_select();
        // 現在選択状態のセルをクリックされたら選択していない状態にする関数でnullにしたら以下の処理が実行されないように
        if(cell_disable(Array.prototype.indexOf.call(tableCells, cell)))
        {
            // boardの何番目がクリックされたかを取得
            cellIndex = Array.prototype.indexOf.call(tableCells, cell);
            // 移動可能な位置を表示する関数
            board_uisupport();
            console.log(cellIndex);
        }
    });
});

// 動かしたい方向を矢印キーで取得
document.addEventListener('keydown', (event) => {
    // クリックされた状態かつ矢印キーが押された状態であれば
    if (cellIndex !== null) {
        // 盤面の要素番号と矢印キーの情報を取得
        move_piece(cellIndex, event.key);
        console.log(event.key);
    }
});

// 要素数チェック関数
function check_cellIndex(cellIndex) {
    // 要素番号が0未満24以上であればerror
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
        // 移動可能な位置を初期化
        clear_select();
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
// 移動可能な位置を表示する関数
function board_uisupport()
{
    console.log("boarduisupport");
    // 移動先の位置を定義
    const moves = [
        // 上
        cellIndex - 4,
        // 下
        cellIndex + 4,
        // 左
        cellIndex - 1,
        // 右
        cellIndex + 1
    ];
    var oyster_board = document.querySelectorAll('.board td');
    // クリックされた位置が自分のオイスターであること
    // 移動先がboardの範囲内かつであること
    // 4で割った結果が1以上であること、移動先が空もしくは相手のオイスターであること
    // 上
    if((oyster_board[cellIndex].innerHTML == '○' || oyster_board[cellIndex].innerHTML == '●') && check_cellIndex(moves[0]) && cellIndex / 4 > 0 && (oyster_board[moves[0]].innerHTML == '■' || oyster_board[moves[0]].innerHTML == '▲'))     
    {
        console.log('up is enable direction');
        oyster_board[moves[0]].classList.add("selected");
    }
    // 下
    // 4で割った結果が6未満であること、移動先が空もしくは相手のオイスターであること
    if((oyster_board[cellIndex].innerHTML == '○' || oyster_board[cellIndex].innerHTML == '●') && check_cellIndex(moves[1]) && cellIndex / 4 < 6 && (oyster_board[moves[1]].innerHTML == '■' || oyster_board[moves[1]].innerHTML == '▲'))     
    {
        console.log('down is enable direction');
        oyster_board[moves[1]].classList.add("selected");
    }
    // 左
    // 4で割った余りが1以上であること、移動先が空もしくは相手のオイスターであること
    if((oyster_board[cellIndex].innerHTML == '○' || oyster_board[cellIndex].innerHTML == '●') && check_cellIndex(moves[2]) && cellIndex % 4 > 0 && (oyster_board[moves[2]].innerHTML == '■' || oyster_board[moves[2]].innerHTML == '▲'))     
    {
        console.log('left is enable direction');
        oyster_board[moves[2]].classList.add("selected");
    }
    // 右
    // 4で割った余りが3未満であること、移動先が空もしくは相手のオイスターであること
    if((oyster_board[cellIndex].innerHTML == '○' || oyster_board[cellIndex].innerHTML == '●') && check_cellIndex(moves[3]) && cellIndex % 4 < 3 && (oyster_board[moves[3]].innerHTML == '■' || oyster_board[moves[3]].innerHTML == '▲'))     
    {
        console.log('right is enable derection');
        oyster_board[moves[3]].classList.add("selected");
    }
}
// 既に選択状態なら選択していない状態にする関数
function cell_disable(cellindex)
{
    // 選択された位置と、現在の位置が同じならnullに
    if(cellindex == cellIndex)
    {
        cellIndex = null;
        console.log("clear select");
        clear_select();
        return false;
    }
    console.log("select oyster");
    return true;
}
function clear_select()
{
    var oyster_board = document.querySelectorAll('.board td');
    for(let i = 0; i < oyster_board.length; i++)
    {
        // 移動可能な位置を初期化
        oyster_board[i].classList.remove("selected");
    }
}