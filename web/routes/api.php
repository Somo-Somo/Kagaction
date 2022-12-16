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
// Route::post('/line-bot/reply', [LineBotController::class, 'reply']);
Route::post('/line-bot/reply', [MockUpController::class, 'reply']);
