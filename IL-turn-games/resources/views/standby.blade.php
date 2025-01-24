<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>standby</title>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        Pusher.logToConsole = true;
        var pusher = new Pusher("{{ config('const.pusher.app_key') }}", {
            cluster: "{{ config('const.pusher.cluster') }}"
        });

        var channel = pusher.subscribe('room.{{$room_id}}');
        channel.bind('preparation', function(data) {
            document.getElementById("match_text").textContent = "PlayerMatched!";
            window.location.href = "{{ url('/oyster/preparation') }}";
        });
    </script>
</head>
<body>
    <h1 id="match_text">マッチング中</h1>
</body>
</html>