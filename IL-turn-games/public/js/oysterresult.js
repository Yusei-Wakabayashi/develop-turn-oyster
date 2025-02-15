function sendmatch()
{
    fetch(`/oyster/match`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json()) // サーバーからのレスポンスをJSONで受け取る
    .then(data => {
        if(data['room_id'])
        {
            window.location.href = '/oyster/standby';
        }
        if(data['game_id'])
        {
            window.location.href = '/oyster/preparation';
        }
    })
}