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
   * 認可API
   *
   * 認可コードは1度使用すると使えなくなるため、同じURIにアクセスするとエラーとなる。
   *
   * @param HttpRequest $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function auth(HttpRequest $request) {
    $code = $request->input('code');

    // 認可コードとトークンを交換
    $tokens = CognitoService::requestToken($code, route('auth'));
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

    return response()->json([
      'status' => 'ok',
    ]);
  }

  /**
   * ログイン済みユーザの情報を取得.
   *
   * @param HttpRequest $request
   * @return void
   */
  public function user(HttpRequest $request) {
    $user = $request->user();
    return response()->json([
      'id' => $user->id,
      'name' => $user->name,
    ]);
  }
}
