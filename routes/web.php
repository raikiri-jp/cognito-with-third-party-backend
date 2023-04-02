<?php

use App\Services\Cognito\CognitoService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

Route::get('/', function () {
  return view('welcome');
})->name('frontend');

Route::get('/login', function () {
  $uri = CognitoService::makeLoginUri(route('auth'));
  return redirect($uri);
})->name('login');

Route::get('/logout-to-login', function () {
  $uri = CognitoService::makeLogoutToLoginUri(route('auth'));
  return redirect($uri);
});

Route::get('/logout', function () {
  $uri = CognitoService::makeLogoutUri(route('frontend'));
  return redirect($uri);
})->name('logout');

Route::get('/auth', function (Request $request) {
  $code = $request->input('code');
  $token = CognitoService::fetchToken($code, route('auth'));
  echo '<pre>';
  echo $token;
  echo '</pre>';
  if ($token->status() !== 200) {
    // 認可コードが既に消費されている
    throw new AuthenticationException();
  }
  $idToken = $token['id_token'];
  $accessToken = $token['access_token'];
  $refreshToken = $token['refresh_token'];
  echo "<div>ID Token: $idToken</div>";
  echo "<div>Access Token: $accessToken</div>";
  echo "<div>Refresh Token: $refreshToken</div>";

  $userInfo = CognitoService::fetchUser($accessToken);
  echo '<pre>';
  echo $userInfo;
  echo '</pre>';
  if ($token->status() !== 200) {
    throw new Exception();
  }
  $sub = $userInfo['sub'];
  $username = $userInfo['username'];
  echo "<div>Sub: $sub</div>";
  echo "<div>User Name: $username</div>";
  // $email = $userInfo['email'];
  // $emailVerified = $userInfo['email_verified'];
  // echo "<div>Email: $email</div>";
  // echo "<div>Email Verified: $emailVerified</div>";
  // $phoneNumber = $userInfo['phone_number'];
  // $phoneNumberVerified = $userInfo['phone_number_verified'];
  // echo "<div>Phone Number: $phoneNumber</div>";
  // echo "<div>Phone Number Verified: $phoneNumberVerified</div>";
})->name('auth');
