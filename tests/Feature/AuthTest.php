<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 認証
     * ========================================================================= */

    // ==========================================
    // ■ 会員登録
    // ==========================================

    // 会員登録画面にアクセスした際、有効な値で登録するとログイン状態で書籍一覧画面（/）へリダイレクトされる。
    public function test_user_can_register_with_valid_data()
    {
        $registerData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $registerData);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $this->assertAuthenticated();
    }

    // バリデーションに失敗した際、エラーメッセージがセッション（画面）に表示され、DBにユーザーが作成されない。
    public function test_registration_fails_with_invalid_data()
    {
        $invaliData = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $response = $this->post(route('register'), $invaliData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
    }

    // ==========================================
    // ■ ログイン
    // ==========================================

    // 正しい情報を送信した際、ログインが完了し書籍一覧画面へリダイレクトされる
    public function test_user_can_login_with_valid_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('login'), $credentials);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.index'));
        $this->assertAuthenticatedAs($user);
    }

    // 登録されていない情報を送信した際、エラーメッセージがセッション（画面）に表示され、認証に失敗し未認証状態（ゲスト）が維持される。
    public function test_login_fails_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $invalidCredentials = [
            'email' => 'registered@example.com',
            'password' => 'wrong-password',
        ];

        $response = $this->post(route('login'), $invalidCredentials);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    // ==========================================
    // ■ 権限・アクセス制限
    // ==========================================

    // 既にログイン済みの状態で会員登録・ログイン画面にアクセスした際、書籍一覧画面に自動的にリダイレクトされる
    public function test_authenticated_user_is_redirected_from_auth_pages()
    {
        $user = User::factory()->create();

        $responseLogin = $this->actingAs($user)->get(route('login'));
        $responseLogin->assertStatus(302);
        $responseLogin->assertRedirect(route('books.index'));

        $responseRegister = $this->actingAs($user)->get(route('register'));
        $responseRegister->assertStatus(302);
        $responseRegister->assertRedirect(route('books.index'));
    }

    // ==========================================
    // ■ ログアウト
    // ==========================================

    // ログイン中のユーザーがログアウトボタンを押すと、セッションが破棄されログイン画面にリダイレクトされる
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
