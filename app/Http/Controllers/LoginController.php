<?php

namespace App\Http\Controllers;

use App\Services\Cognito\CognitoService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers
 * @author Miyahara Yuuki <59301668+raikiri-jp@users.noreply.github.com>
 */
class LoginController extends Controller {

  /**
   * ログイン画面の表示.
   *
   * Amazon Cognito によりホストされたログイン画面を表示する。
   *
   * @param HttpRequest $request
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function login(HttpRequest $request) {
    // ログアウトしてセッションを破棄
    Auth::logout();
    session()->flush();

    // ログイン画面にリダイレクト
    return redirect(CognitoService::getLoginUri(route('auth')));
  }

  /**
   * ログアウト後にログイン画面を表示.
   *
   * ログアウトエンドポイント経由で Amazon Cognito によりホストされたログイン画面を表示する。
   *
   * @param HttpRequest $request
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function reLogin(HttpRequest $request) {
    // ログアウトしてセッションを破棄
    Auth::logout();
    session()->flush();
    // Cognito側でもログアウトして、ログイン画面を表示する
    return redirect(CognitoService::getReLoginUri(route('auth')));
  }

  /**
   * ログアウト後に任意の画面を表示.
   *
   * @param HttpRequest $request
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function logout(HttpRequest $request) {
    // ログアウトしてセッションを破棄
    Auth::logout();
    session()->flush();
    // ログアウト後に任意の画面を表示
    return redirect(CognitoService::getLogoutUri(route('frontend')));
  }
}
