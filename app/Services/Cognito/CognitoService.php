<?php

namespace App\Services\Cognito;

use Illuminate\Support\Facades\Http;

/**
 * Amazon Cognito Service.
 *
 * @author Miyahara Yuuki <59301668+raikiri-jp@users.noreply.github.com>
 */
class CognitoService {
  // TODO scopeについては保留 (優先度:低)
  // /**
  //  * デフォルトスコープの取得.
  //  *
  //  * @return array ログイン時にデフォルトで指定するスコープ
  //  */
  // protected static function getDefaultScopes() {
  //   return explode(',', env('COGNITO_SCOPES', 'openid,profile'));
  // }

  /**
   * ログイン画面のURIを求める.
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return string Amazon Cognito によりホストされたログイン画面のURI
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/cognito-user-pools-app-integration.html
   */
  public static function getLoginUri(string $redirectUri, array $scopes = []) {
    return env('COGNITO_OAUTH2_DOMAIN') . '/login?' . http_build_query([
      'client_id' => env('COGNITO_APP_CLIENT_ID'),
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
  }

  /**
   * ログアウトエンドポイントを経由してログイン画面を表示するURIを求める.
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return string Amazon Cognito によりホストされたログイン画面のURI (ログアウトエンドポイントを経由)
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/logout-endpoint.html
   */
  public static function getReLoginUri(string $redirectUri, array $scopes = []) {
    return env('COGNITO_OAUTH2_DOMAIN') . '/logout?' . http_build_query([
      'client_id' => env('COGNITO_APP_CLIENT_ID'),
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
  }

  /**
   * ログアウトエンドポイントのURIを求める.
   *
   * @param string $redirectUri リダイレクト先URI
   * @return string ログアウトエンドポイントのURI
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/logout-endpoint.html
   */
  public static function getLogoutUri(string $redirectUri) {
    return env('COGNITO_OAUTH2_DOMAIN') . '/logout?' . http_build_query([
      'client_id' => env('COGNITO_APP_CLIENT_ID'),
      'logout_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
  }

  /**
   * トークンの問合せ.
   *
   * 認可コードは1度使用すると使えなくなるため、同じURIにアクセスするとエラーとなる。
   * 当メソッド利用後に別URIにリダイレクトすることでエラーを回避できる。
   *
   * @param string $authorizationCode 認可コード
   * @param string $redirectUri ログイン時に指定したリダイレクトURI (ログイン後の遷移先)
   * @return array `id_token`、`access_token`、`refresh_token`、`expires_in` を含む配列
   * @throws \Illuminate\Auth\AuthenticationException An error occurred during authorization
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/token-endpoint.html
   */
  public static function requestToken(string $authorizationCode, string $redirectUri): array {
    // トークンエンドポイント
    $uri = env('COGNITO_OAUTH2_DOMAIN') . '/oauth2/token';
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $secret = env('COGNITO_APP_SECRET');
    $headers = [
      'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $secret)
    ];
    $data = [
      'grant_type' => 'authorization_code',
      'code' => $authorizationCode,
      'redirect_uri' => $redirectUri,
    ];
    $cognitoResponse = Http::asForm()->withHeaders($headers)->post($uri, $data);
    $response = $cognitoResponse->json();
    if ($cognitoResponse->failed()) {
      $errorMessage = $response['error_description'] ?? 'An error occurred during authorization';
      throw new \Illuminate\Auth\AuthenticationException($errorMessage);
    }

    return [
      'id_token' => $response['id_token'],
      'access_token' => $response['access_token'],
      'refresh_token' => $response['refresh_token'],
      'expires_in' => $response['expires_in'],
    ];
  }

  /**
   * ユーザ属性の問合せ.
   *
   * @param string $accessToken Access Token
   * @return array ユーザ属性
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/userinfo-endpoint.html
   */
  public static function requestUserInfo(string $accessToken) {
    // UserInfo エンドポイント
    $uri = env('COGNITO_OAUTH2_DOMAIN') . '/oauth2/userInfo';
    $userInfo = Http::withToken($accessToken)->get($uri);
    return [
      'sub' => $userInfo['sub'],
      'email' => @$userInfo['email'],
      'username' => $userInfo['username'],
      'name' => @$userInfo['name'],
      'family_name' => @$userInfo['family_name'],
      'given_name' => @$userInfo['given_name'],
      'department' => @$userInfo['custom:department'],
      'position' => @$userInfo['custom:position'],
    ];
  }

  /**
   * アクセストークンの更新.
   *
   * @return void
   */
  public static function refreshAccessToken(): void {
    // TODO リフレッシュトークンを使用してアクセストークンを更新する
    throw new \RuntimeException('Not implemented');
  }
}
