<?php

use App\Services\Cognito\CognitoService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
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
  $loggedIn = false;
  $userInfo = [];
  try {
    CognitoService::refreshToken();
    $userInfo = CognitoService::getUser();
    $loggedIn = true;
  } catch (AuthenticationException) {
    // return redirect(route('login'));
  }
  return view('welcome', [
    'loggedIn' => $loggedIn,
    'userInfo' => $userInfo,
  ]);
});

// ログイン
Route::name('login')->get('/login', function () {
  // ログイン画面にリダイレクト
  return CognitoService::toLogin(route('auth'));
});

// ログアウト
Route::name('logout')->get('/logout', function () {
  // ログアウト後に任意の画面を表示
  return CognitoService::logout(route('frontend'));

  // ログアウト後にログイン画面を表示
  // return CognitoService::logoutAndLogin(route('auth'));
});

Route::name('auth')->get('/auth', function (Request $request) {
  // 認可
  CognitoService::authorize($request->input('code'), route('auth'));
  return redirect(route('frontend'));
});
