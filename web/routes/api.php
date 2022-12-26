<?php

use App\Http\Controllers\Api\MockUpController;
use App\Http\Controllers\ImageReportController;
use App\Http\Controllers\DebugController;
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
Route::get('/mockup', [MockUpController::class, 'debug']);
Route::post('/debug', [DebugController::class, 'debug']);
Route::get('/report/monthly/{id}', [ImageReportController::class, 'index']);
