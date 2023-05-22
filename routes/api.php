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

// ログインチェックAPI
Route::get('/auth/check', [AuthController::class, 'check']);

// ユーザ情報取得API
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//   return $request->user();
// });
