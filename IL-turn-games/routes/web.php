<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OysterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/oyster/title', function () {
    return view('title');
});

Route::get('/oyster/match', [
    OysterController::class,
    'match',
]);

Route::get('/oyster/id/room', [
    OysterController::class,
    'get_room_id',
]);

Route::get('/oyster/standby', function () {
    return view('standby');
});

Route::get('/oyster/id/preparation', [
    OysterController::class,
    'get_preparation',
]);

Route::get('/oyster/preparation', function () {
    return view('preparation');
});

Route::get('/oyster/first', [
    OysterController::class,
    'first_turn',
]);

Route::post('/oyster/start', [
    OysterController::class,
    'start_game',
]);

Route::get('/oyster/id/game', [
    OysterController::class,
    'get_game_id',
]);

Route::get('/oyster/game', function () {
    return view('game');
});

Route::get('/oyster/info', [
    OysterController::class,
    'show_info',
]);

Route::post('/oyster/move', [
    OysterController::class,
    'move',
]);

Route::get('/oyster/result/{game_id}', [
    OysterController::class,
    'show_result',
]);

Route::get('/oyster/status', [
    OysterController::class,
    'retrun_player_status',
]);

Route::get('/oyster/win', function() {
    return view('win');
});

Route::get('/oyster/lose', function() {
    return view('lose');
});

Route::get('/oyster/change/result', [
    OysterController::class,
    'show_change_result',
]);

Route::get('/oyster/image/result', function() {
    return view('result');
});

Route::get('/oyster/number/{game_id}', [
    OysterController::class,
    'get_oyster_number_result',
]);

Route::get('/test', [
    OysterController::class,
    'show_request',
]);

Route::get('/reset/session', [
    OysterController::class,
    'delete_session',
]);