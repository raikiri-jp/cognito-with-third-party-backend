<?php

namespace App\Services\Cognito;

use Illuminate\Support\Facades\Http;

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
   * Amazon Cognito によりホストされたログイン画面のURIを生成.
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return string Amazon Cognito によりホストされたログイン画面のURI
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/cognito-user-pools-app-integration.html
   */
  public static function makeLoginUri(string $redirectUri, array $scopes = []) {
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/login?" . http_build_query([
      'client_id' => $clientId,
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return $uri;
  }

  /**
   * ログアウト用URIを生成.
   *
   * ログアウトエンドポイントのURIを作成する。
   * ブラウザがURIにアクセスすると、ログアウトされた後、指定されたURIにリダイレクトされる。
   *
   * @param boolean $logoutUri
   * @return string ログアウトエンドポイントのURI
   */
  public static function makeLogoutUri(string $logoutUri) {
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/logout?" . http_build_query([
      'client_id' => $clientId,
      'logout_uri' => $logoutUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return $uri;
  }

  /**
   * ログアウト後にログイン画面を表示するURIを生成.
   *
   * ログアウトエンドポイントのURIを作成する。
   * ブラウザがURIにアクセスすると、ログアウトされた後、ログイン画面にリダイレクトされる。
   *
   * @param string $redirectUri ログイン後のリダイレクト先URI
   * @param array $scopes スコープ (スコープはUserInfo エンドポイントで取得できる内容に影響する)
   * @return string ログアウトエンドポイントのURI
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/logout-endpoint.html
   */
  public static function makeLogoutToLoginUri(string $redirectUri, array $scopes = []) {
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $clientId = env('COGNITO_APP_CLIENT_ID');
    $uri = "https://$domain/logout?" . http_build_query([
      'client_id' => $clientId,
      'response_type' => 'code',
      'redirect_uri' => $redirectUri,
    ], '', '&', PHP_QUERY_RFC1738);
    return $uri;
  }

  /**
   * 認可コードとトークンの交換.
   *
   * トークンエンドポイントに認可コードを渡してトークンを取得する。
   *
   * @param string $authorizationCode 認可コード
   * @param string $redirectUri ログイン時に指定したリダイレクトURI (ログイン後の遷移先)
   * @return \Illuminate\Http\Client\Response
   * @see https://docs.aws.amazon.com/ja_jp/cognito/latest/developerguide/token-endpoint.html
   */
  public static function fetchToken(string $authorizationCode, string $redirectUri) {
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
    return Http::asForm()->withHeaders($headers)->post($uri, $data);
  }

  /**
   * ユーザ属性の取得.
   *
   * UserInfo エンドポイントからユーザ属性を取得.
   *
   * @param string $accessToken
   * @return \Illuminate\Http\Client\Response
   */
  public static function fetchUser(string $accessToken) {
    $domain = env('COGNITO_OAUTH2_DOMAIN');
    $uri = "https://$domain/oauth2/userInfo";
    return Http::withToken($accessToken)->get($uri);
  }
}
