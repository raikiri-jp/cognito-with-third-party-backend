<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 認可API
Route::get('/auth', [AuthController::class, 'auth']);

// 認証を必要とするAPIのグループ
Route::middleware('auth:sanctum')->group(function () {
  // ログインユーザ情報取得API
  Route::get('/user', [AuthController::class, 'user']);
});
