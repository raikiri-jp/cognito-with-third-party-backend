<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
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

// ログイン画面の表示
Route::name('login')->get('/login', [LoginController::class, 'login']);
// ログアウト後にWelcomeページ表示
Route::name('logout')->get('/logout', [LoginController::class, 'logout']);
// ログアウト後にログイン画面を表示
Route::get('/re-login', [LoginController::class, 'reLogin']);

// Frontend
Route::name('auth')->get('/auth', [IndexController::class, 'frontend']);
Route::name('frontend')->get('/', [IndexController::class, 'frontend'])
  ->where('any', '.*');
Route::get('/{any}', [IndexController::class, 'frontend'])
  ->where('any', '.*');
