<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

// Welcomeページ表示
Route::name('frontend')->get('/', function () {
  $loggedIn = Auth::check();
  $userInfo = $loggedIn ? Auth::getUser() : [];
  return view('welcome', [
    'loggedIn' => $loggedIn,
    'userInfo' => $userInfo,
  ]);
});
// ログイン画面の表示
Route::name('login')->get('/login', [AuthController::class, 'login']);
// ログアウト後にWelcomeページ表示
Route::name('logout')->get('/logout', [AuthController::class, 'logout']);
// ログアウト後にログイン画面を表示
Route::get('/re-login', [AuthController::class, 'reLogin']);
// 認可処理 (認証成功時の遷移先)
Route::name('auth')->get('/auth', [AuthController::class, 'auth']);
