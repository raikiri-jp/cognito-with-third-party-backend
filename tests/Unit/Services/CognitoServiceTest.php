<?php

namespace Tests\Feature;

use App\Services\Cognito\CognitoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CognitoServiceTest extends TestCase {
  protected function setUp(): void {
    parent::setUp();

    env('COGNITO_OAUTH2_DOMAIN', 'https://example.com');
    env('COGNITO_APP_CLIENT_ID', 'your_client_id');
    env('COGNITO_APP_SECRET', 'your_client_secret');
  }

  public function testGetLoginUri(): void {
    $redirectUri = 'https://example.com/callback';
    $expectedUri = 'https://example.com/login?' .
      'client_id=your_client_id' .
      '&response_type=code' .
      '&redirect_uri=' . urlencode($redirectUri);

    $uri = CognitoService::getLoginUri($redirectUri);

    $this->assertEquals($expectedUri, $uri);
  }

  public function testGetReLoginUri(): void {
    $redirectUri = 'https://example.com/callback';
    $expectedUri = 'https://example.com/logout?' .
      'client_id=your_client_id' .
      '&response_type=code' .
      '&redirect_uri=' . urlencode($redirectUri);

    $uri = CognitoService::getReLoginUri($redirectUri);

    $this->assertEquals($expectedUri, $uri);
  }

  public function testGetLogoutUri(): void {
    $redirectUri = 'https://example.com/logout';
    $expectedUri = 'https://example.com/logout?' .
      'client_id=your_client_id' .
      '&logout_uri=' . urlencode($redirectUri);

    $uri = CognitoService::getLogoutUri($redirectUri);

    $this->assertEquals($expectedUri, $uri);
  }

  public function testRequestToken(): void {
    // テストに使用するダミーレスポンスデータ
    $tokenData = [
      'id_token' => 'dummy-id-token',
      'access_token' => 'dummy-access-token',
      'refresh_token' => 'dummy-refresh-token',
      'expires_in' => 3600,
    ];

    // モックされた HTTP レスポンスを作成
    $mockResponse = Http::response($tokenData);

    // Token エンドポイントの URL
    $tokenUrl = 'https://example.com/oauth2/token';

    // Authorization Code
    $authorizationCode = 'dummy-authorization-code';

    // Redirect URI
    $redirectUri = 'https://example.com/callback';

    // Http ファサードのモック
    Http::fake([
      $tokenUrl => $mockResponse,
    ]);

    // requestToken メソッドを呼び出す
    $result = CognitoService::requestToken($authorizationCode, $redirectUri);

    // モックされた HTTP リクエストをアサート
    Http::assertSent(function ($request) use ($tokenUrl, $authorizationCode, $redirectUri) {
      $basic = 'Basic ' . base64_encode('your_client_id:your_client_secret');
      return $request->url() === $tokenUrl
        && $request->method() === 'POST'
        && $request->header('Authorization') === [$basic]
        && $request['grant_type'] === 'authorization_code'
        && $request['code'] === $authorizationCode
        && $request['redirect_uri'] === $redirectUri;
    });

    // 結果のアサート
    $this->assertEquals($tokenData['id_token'], $result['id_token']);
    $this->assertEquals($tokenData['access_token'], $result['access_token']);
    $this->assertEquals($tokenData['refresh_token'], $result['refresh_token']);
    $this->assertEquals($tokenData['expires_in'], $result['expires_in']);
  }

  public function testRequestUserInfo(): void {
    // テスト用のダミーレスポンス
    $dummyUserInfo = [
      'sub' => 'dummy_sub',
      'email' => 'dummy@example.com',
      'username' => 'dummy_user',
      'name' => 'Dummy User',
      'family_name' => 'Dummy',
      'given_name' => 'User',
      'custom:department' => 'IT',
      'custom:position' => 'Engineer',
    ];

    // Http::getのモックを作成し、テスト用のレスポンスを設定する
    Http::fake([
      '*' => Http::response($dummyUserInfo),
    ]);

    $accessToken = 'dummy_access_token';

    $userInfo = CognitoService::requestUserInfo($accessToken);

    $this->assertEquals($dummyUserInfo['sub'], $userInfo['sub']);
    $this->assertEquals($dummyUserInfo['email'], $userInfo['email']);
    $this->assertEquals($dummyUserInfo['username'], $userInfo['username']);
    $this->assertEquals($dummyUserInfo['name'], $userInfo['name']);
    $this->assertEquals($dummyUserInfo['family_name'], $userInfo['family_name']);
    $this->assertEquals($dummyUserInfo['given_name'], $userInfo['given_name']);
    $this->assertEquals($dummyUserInfo['custom:department'], $userInfo['department']);
    $this->assertEquals($dummyUserInfo['custom:position'], $userInfo['position']);

    // リクエストが期待通りに行われたか
    Http::assertSent(function ($request) use ($accessToken) {
      $bearer = 'Bearer ' . $accessToken;
      return $request->url() === 'https://example.com/oauth2/userInfo'
        && $request->method() === 'GET'
        && $request->header('Authorization') === [$bearer];
    });
  }

  public function testRefreshAccessToken(): void {
    $this->expectException(\RuntimeException::class);

    CognitoService::refreshAccessToken();
  }
}
