<?php

use App\Http\Controllers\Api\MockUpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// APIのURL以外のリクエストに対してはindexテンプレートを返す
// 画面遷移はフロントエンドのVueRouterが制御する
Route::get('/api/{any?}', fn () => view('index'))->where('any', '.+');
Route::get('/weekly_report', fn () => view('index'));
// Route::post('/line-bot/reply', [MockUpController::class, 'reply']);
