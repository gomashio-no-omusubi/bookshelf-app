<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessBookTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 画面アクセス
     * ========================================================================= */

    // ゲスト（未ログイン）が書籍一覧画面にアクセスできる
    public function test_guest_can_access_book_index_page()
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
    }

    // ログイン済みユーザーが書籍一覧画面にアクセスできる
    public function test_authenticated_user_can_access_book_index_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);
    }

    // ログイン済みの場合、書籍登録画面に遷移できる
    public function test_authenticated_user_sees_link_to_create_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertSee(route('books.create'));
    }

    // 未ログインの場合は、書籍登録画面に遷移できずログイン画面（/login）にリダイレクトされる
    public function test_guest_cannot_access_create_page_and_redirects_to_login()
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }
}
