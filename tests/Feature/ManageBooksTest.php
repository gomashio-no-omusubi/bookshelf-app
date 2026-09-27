<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageBooksTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 書籍CRUD
     * ========================================================================= */

    // ==========================================
    // ■ 書籍詳細
    // ==========================================

    // ログイン済みユーザーが書籍詳細ページにアクセスできる。書籍タイトルが画面に含まれる。

    public function test_show_page_displays_book_title()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->get(route('books.show', $book));

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    // ==========================================
    // ■ 書籍登録
    // ==========================================

    // ログイン済のユーザーの場合、有効な入力値で書籍を登録でき、中間テーブル（book_genre）に選択したジャンルが正しく連動・同期される
    public function test_authenticated_user_can_register_book_with_genres()
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $bookData = [
            'title' => 'テスト書籍タイトル',
            'author' => 'テスト著者名',
            'isbn' => '9784798157573',
            'published_date' => '2026-01-01',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->post(route('books.store'), $bookData);

        $latestBook = Book::latest()->first();
        $response->assertRedirect(route('books.show', $latestBook));

        $this->assertDatabaseHas('books', ['title' => 'テスト書籍タイトル']);
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genres', [
                'book_id' => $latestBook->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    // バリデーションエラー時は、適切に登録をブロックしてエラー応答を返す挙動を担保
    public function test_book_registration_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    // ==========================================
    // ■ 書籍編集
    // ==========================================

    // 書籍の所有者本人のみが編集→更新（ジャンルの sync 処理含む）を実行できる
    public function test_owner_can_update_book_and_genres()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $newGenres = Genre::factory()->count(2)->create();

        $updatedData = [
            'title' => '変更後のタイトル',
            'author' => 'テスト著者名',
            'isbn' => '9784798157573',
            'published_date' => '2026-01-01',
            'genres' => $newGenres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put(route('books.update', $book), $updatedData);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $book));

        foreach ($newGenres as $genre) {
            $this->assertDatabaseHas(
                'book_genres',
                [
                    'book_id' => $book->id,
                    'genre_id' => $genre->id,
                ]
            );
        }
    }

    // 他ユーザーの書籍に対する編集リクエストは、システム側で適切に 403 Forbidden の拒否レスポンスを返す
    public function test_other_user_cannot_edit_book()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('books.update', $book), [
            'title' => '勝手に変更するタイトル',
            'author' => 'テスト著者',
            'isbn' => '9784798157573',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }

    // ==========================================
    // ■ 書籍削除
    // ==========================================

    // 書籍の所有者本人のみが削除を実行でき、処理後は一覧画面に正常にリダイレクトされる。DBからレコードが削除される。
    public function test_owner_can_delete_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('books.destroy', $book));

        $response->assertStatus(302);
        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    // 他ユーザーの書籍に対する削除リクエストは、システム側で適切に 403 Forbidden の拒否レスポンスを返す
    public function test_other_user_cannot_delete_book()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $response->assertStatus(403);
    }
}
