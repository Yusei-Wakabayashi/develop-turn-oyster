<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>lose_image</title>
</head>
<body>
    <h1>you lose</h1>
    <form action="{{ url('/oyster/match') }}" method="GET">
        @csrf
        <label for="">もう一度</label>
        <button type="submit" class="btn btn-primary">開始</button>
    </form>
    <form action="{{ url('/oyster/title') }}" method="GET">
        @csrf
        <label for="">タイトルに戻る</label>
        <button type="submit" class="btn btn-primary">終了</button>
    </form>
</body>
</html>