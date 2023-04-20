<?php

use App\Services\Cognito\CognitoService;
use Illuminate\Http\Request;
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

Route::name('frontend')->get('/', function () {
  $loggedIn = Auth::check();
  $userInfo = $loggedIn ? Auth::getUser() : [];
  return view('welcome', [
    'loggedIn' => $loggedIn,
    'userInfo' => $userInfo,
  ]);
});

// ログイン画面
Route::name('login')->get('/login', function () {
  // ログイン画面にリダイレクト
  return CognitoService::toLogin(route('auth'));
});

// ログアウト
Route::name('logout')->get('/logout', function () {
  // ログアウト後に任意の画面を表示
  return CognitoService::logout(route('frontend'));
});

// 認可
Route::name('auth')->get('/auth', function (Request $request) {
  $user = CognitoService::authorize($request->input('code'), route('auth'));
  Auth::login($user);
  return redirect(route('frontend'));
});
