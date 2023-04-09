<?php

namespace App\Services\Cognito;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Http;

use function Psy\debug;

/**
 * Cognito Service.
 *
 * @author Miyahara Yuuki <59301668+raikiri-jp@users.noreply.github.com>
 */
class CognitoService {
  // TODO scopeについては保留
  // /**
  //  * デフォルトスコープの取得.
  //  *
  //  * @return array ログイン時にデフォルトで指定するスコープ
  //  */
  // protected static function getDefaultScopes() {
  //   return explode(',', env('COGNITO_SCOPES', 'openid,profile'));
  // }

  /**
   * ログイン画面の表示.
   *
   * Amazon Cognito によりホストされたログイン画面を表示する。
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/cognito-user-pools-app-integration.html
   */
  public static function toLogin(string $redirectUri, array $scopes = []) {
    // セッションを破棄
    session()->flush();
    // CognitoのUIを利用してログイン
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/login?" . http_build_query([
      'client_id' => $clientId,
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return redirect($uri);
  }

  /**
   * ログアウト後に任意の画面にリダイレクト.
   *
   * @param string $redirectUri リダイレクト先URI
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public static function logout(string $redirectUri) {
    // セッションを破棄
    session()->flush();
    // Cognito側でもログアウトし、指定のURIにリダイレクトする
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/logout?" . http_build_query([
      'client_id' => $clientId,
      'logout_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return redirect($uri);
  }

  /**
   * ログアウト後にログイン画面を表示.
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public static function logoutAndLogin(string $redirectUri, array $scopes = []) {
    // セッションを破棄
    session()->flush();
    // Cognito側でもログアウトして、ログイン画面を表示する
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/logout?" . http_build_query([
      'client_id' => $clientId,
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return redirect($uri);
  }

  /**
   * 認可コードとトークンの交換.
   *
   * 認可コードは1度使用すると使えなくなるため、同じURIにアクセスするとエラーとなる。
   * 当メソッド利用後に別URIにリダイレクトすることでエラーを回避できる。
   *
   * @param string $authorizationCode 認可コード
   * @param string $redirectUri ログイン時に指定したリダイレクトURI (ログイン後の遷移先)
   * @throws AuthenticationException 認可失敗時にスローされる
   * @return void
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/token-endpoint.html
   */
  public static function authorize(string $authorizationCode, string $redirectUri) {
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $secret = env('COGNITO_APP_SECRET');
    $uri = "https://$domain/oauth2/token";
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $secret)
    ];
    $data = [
      'grant_type' => 'authorization_code',
      'code' => $authorizationCode,
      'redirect_uri' => $redirectUri,
    ];

    $token = Http::asForm()->withHeaders($headers)->post($uri, $data);
    if ($token->status() !== 200) {
      // 認可コードが既に消費されているケース
      throw new AuthenticationException();
    }

    $data = [
      'id_token' => $token['id_token'],
      'access_token' => $token['access_token'],
      'refresh_token' => $token['refresh_token'],
      'expires_in' => $token['expires_in'],
      'updated' => now(),
    ];
    session(['auth' => $data]);
  }

  /**
   * アクセストークンの取得.
   *
   * @return string Access token
   * @throws AuthenticationException 認可されてない場合にスローされる
   */
  public static function getAccessToken() {
    $accessToken = session('auth.access_token');
    if (empty($accessToken)) {
      throw new AuthenticationException();
    }
    return $accessToken;
  }

  /**
   * 更新トークンの取得.
   *
   * @return string Refresh token
   */
  public static function getRefreshToken() {
    $refreshToken = session('auth.refresh_token');
    if (empty($refreshToken)) {
      throw new AuthenticationException();
    }
    return $refreshToken;
  }

  /**
   * アクセストークンの更新.
   *
   * @return void
   */
  public static function refreshToken() {
    // FIXME
    return true;
  }

  /**
   * ユーザ属性の取得.
   *
   * @return array ユーザ属性
   * @throws AuthenticationException 認可されてない場合にスローされる
   */
  public static function getUser() {
    $user = session('user');
    if (empty($user)) {
      $user = self::fetchUser();
    }
    return $user;
  }

  /**
   * ユーザ属性の問合せ.
   *
   * @return array ユーザ属性
   * @throws AuthenticationException 認可されてない場合にスローされる
   */
  protected static function fetchUser() {
    $accessToken = self::getAccessToken();
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $uri = "https://$domain/oauth2/userInfo";
    $userInfo = Http::withToken($accessToken)->get($uri);

    $data = [
      'sub' => $userInfo['sub'],
      'username' => $userInfo['username'],
      'name' => $userInfo['family_name'] . ' ' . $userInfo['given_name'],
      'department' => $userInfo['custom:department'],
      'position' => $userInfo['custom:position'],
    ];
    session(['user' => $data]);
    return $data;
  }
}
