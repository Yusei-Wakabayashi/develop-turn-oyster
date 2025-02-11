<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>oyster_game</title>
</head>
<body>
    <script src="{{asset('js/oystertitle.js')}}"></script>
    <h1>oyster_game</h1>
    <label for="">対人</label>
    <button id="sendbutton">開始</button>
    <script>
        var sendbutton = document.getElementById("sendbutton");
        sendbutton.addEventListener("click", function() {
            sendmatch();
        });
    </script>
</body>
</html>