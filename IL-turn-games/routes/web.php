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

Route::get('/oyster/standby', [
    OysterController::class,
    'check_standby',
]);

Route::get('/oyster/preparation', [
    OysterController::class,
    'check_preparation',
]);

Route::get('/oyster/first', [
    OysterController::class,
    'first_turn',
]);

Route::post('/oyster/start', [
    OysterController::class,
    'start_game',
]);

Route::get('/oyster/game', [
    OysterController::class,
    'check_game',
]);

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
])

Route::get('/oyster/win', function() {
    return view('win');
});

Route::get('/oyster/lose', function() {
    return view('lose');
});

Route::get('/test', [
    OysterController::class,
    'show_request',
]);

Route::get('/reset/session', [
    OysterController::class,
    'delete_session',
]);