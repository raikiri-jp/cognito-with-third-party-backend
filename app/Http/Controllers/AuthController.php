<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\Token;
use App\Models\User;
use App\Services\Cognito\CognitoService;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers
 * @author Miyahara Yuuki <59301668+raikiri-jp@users.noreply.github.com>
 */
class AuthController extends Controller {

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

  /**
   * 認可処理.
   *
   * 認可コードは1度使用すると使えなくなるため、同じURIにアクセスするとエラーとなる。
   * ブラウザリロードによるエラー発生を回避するため、処理後にリダイレクトを行う。
   *
   * @param HttpRequest $request
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function auth(HttpRequest $request) {
    // 認可コードとトークンを交換
    $tokens = CognitoService::requestToken($request->input('code'), route('auth'));
    $accessToken = $tokens['access_token'];
    $refreshToken = $tokens['refresh_token'];
    $expiresIn = $tokens['expires_in'];

    // UserInfo エンドポイントよりユーザ属性を取得
    $userInfo = CognitoService::requestUserInfo($accessToken);
    $sub = $userInfo['sub'];
    $email = $userInfo['email'];
    $name = $userInfo['name'];

    // ユーザ情報をユーザテーブルに登録
    $user = User::updateOrCreate([
      'email' => $email
    ], [
      'name' => $name,
      'sub' => $sub,
    ]);

    // アクセストークンとリフレッシュトークンをトークンテーブルに登録
    $token = new Token();
    $token->user_id = $user->id;
    $token->access_token = $accessToken;
    $token->refresh_token = $refreshToken;
    $token->expires_at = Carbon::now()->addSeconds($expiresIn);
    $token->save();

    // ログイン履歴をログイン履歴テーブルに登録
    // TODO ログイン履歴が肥大化しすぎないよう、程よいところで削除する仕組みを作ること
    $loginHistory = new LoginHistory();
    $loginHistory->user_id = $user->id;
    $loginHistory->ip_address = Request::ip();
    $loginHistory->login_at = Carbon::now();
    $loginHistory->save();

    // Laravel の機能を使ってログイン
    Auth::login($user);

    // 再アクセスによるエラーを防止するためにリダイレクトを行う
    return redirect(route('frontend'));
  }

  /**
   * ログイン済みユーザの情報を取得.
   *
   * @param HttpRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function check(HttpRequest $request) {
    if (auth()->check()) {
      return response()->json([
        'status' => 'ok',
      ]);
    } else {
      return response()->json([
        'status' => 'error',
        'message' => 'Unauthenticated.'
      ], 401);
    }
  }

  /**
   * ログイン済みユーザの情報を取得.
   *
   * @param HttpRequest $request
   * @return void
   */
  public function getUser(HttpRequest $request) {
    $user = $request->user();
    return response()->json([
      'id' => $user->id,
      'name' => $user->name,
    ]);
  }
}
