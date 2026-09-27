<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteBooksTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * お気に入り
     * ========================================================================= */

    // ログインユーザーが自身のお気に入り書籍一覧を正常に閲覧できる。
    public function test_authenticated_user_can_view_favorite_books_index()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertViewHas('books');
        $response->assertSee($book->title);
    }

    // 未認証（ゲスト）ユーザーが操作を試みた場合、処理を中断してログイン画面へ自動リダイレクトされる。
    public function test_guest_is_redirected_to_login_screen()
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }

    // 認証ユーザーの場合、お気に入りを追加でき、favoritesテーブルにレコードが作成される。
    public function test_authenticated_user_can_add_book_to_favorites()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));

        $response->assertStatus(302);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // 認証ユーザーの場合、お気に入りを解除でき、favoritesテーブルからレコードが削除される。
    public function test_authenticated_user_can_remove_book_from_favorites()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // お気に入りのトグル（追加→解除→追加）が正しく動作する。
    public function test_favorite_toggle_mechanism_works_correctly()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertStatus(302);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->post(route('favorites.toggle', $book));
        $response->assertStatus(302);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
