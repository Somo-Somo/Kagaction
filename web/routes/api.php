<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\AccomplishController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\DateController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CauseController;
use App\Http\Controllers\Initialize;
use App\Http\Controllers\Onboarding;
use App\Http\Controllers\Api\LineBotController;
use App\Http\Controllers\Api\MockUpController;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// LINE Bot
Route::post('/line-bot/reply', [LineBotController::class, 'reply']);
Route::post('/line-bot/mockup', [MockUpController::class, 'reply']);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/auth_status', [LoginController::class, 'authStatus']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/initialize', Initialize::class);

    // プロジェクト
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/project', [ProjectController::class, 'store']);
    Route::put('/project/{projectId}', [ProjectController::class, 'update']);
    Route::delete('/project/{projectId}', [ProjectController::class, 'destroy']);

    // 仮説
    Route::get('/project', [TodoController::class, 'index']);
    Route::get('/todo/:todoId', [TodoController::class, 'show']);
    Route::post('/todo', [TodoController::class, 'store']);
    Route::put('/todo/{todoId}', [TodoController::class, 'update']);
    Route::delete('/todo/{todoId}', [TodoController::class, 'destroy']);

    // ゴール
    Route::get('/goal', [GoalController::class, 'index']);
    Route::post('/goal', [GoalController::class, 'store']);
    Route::delete('/goal/:todoId', [GoalController::class, 'destroy']);

    // 完了
    Route::put('/todo/{todoId}/accomplish', [AccomplishController::class, 'update']);
    Route::delete('/todo/{todoId}/accomplish', [AccomplishController::class, 'destroy']);

    // 日付
    Route::put('/todo/{todoId}/date', [DateController::class, 'update']);
    Route::delete('/todo/{todoId}/date', [DateController::class, 'destroy']);

    // コメント
    Route::post('/todo/{todoId}/comment', [CommentController::class, 'store']);
    Route::put('/comment/{commentId}/', [CommentController::class, 'update']);
    Route::delete('/comment/{commentId}', [CommentController::class, 'destroy']);

    // 原因
    Route::post('/todo/{todoId}/cause', [CauseController::class, 'store']);
    Route::delete('/cause/{causeId}', [CauseController::class, 'destroy']);

    // オンボーディング
    Route::post('/onboarding', Onboarding::class);
});
