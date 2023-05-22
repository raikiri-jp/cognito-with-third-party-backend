<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase {
  use RefreshDatabase;

  public function testAuthCheck_error() {
    // ログインしていない状態でのリクエスト
    $response = $this->get('/api/auth/check');
    $response->assertStatus(401); // 未認証のため、ステータスコード 401 を期待
    $response->assertJson([
      'status' => 'error',
      'message' => 'Unauthenticated.'
    ]);
  }

  public function testAuthCheck_ok() {
    // ログインしている状態でのリクエスト
    $user = User::create([
      'email' => 'test@example.com',
      'name' => 'test user',
      'sub' => '12345',
    ]);
    $response = $this->actingAs($user)->get('/api/auth/check');
    $response->assertStatus(200); // 認証済みのため、ステータスコード 200 を期待
    $response->assertJson([
      'status' => 'ok',
    ]);
  }
}
