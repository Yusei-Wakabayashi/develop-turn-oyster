<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>oyster_game</title>
</head>
<body>
    <h1>oyster_game</h1>
    <form action="{{ url('/oyster/match') }}" method="GET">
        @csrf
        <label for="">対人</label>
        <button type="submit" class="btn btn-primary">開始</button>
    </form>
</body>
</html>