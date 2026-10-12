<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageBooksTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 書籍CRUD（基本）
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
            'isbn' => fake()->unique()->isbn13(),
            'published_date' => '2026-01-01',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->post(route('books.store'), $bookData);

        $latestBook = Book::latest()->first();
        $response->assertRedirect(route('books.show', $latestBook));

        $this->assertDatabaseHas('books', ['title' => 'テスト書籍タイトル']);
        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
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
            'isbn' => fake()->unique()->isbn13(),
            'published_date' => '2026-01-01',
            'genres' => $newGenres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)->put(route('books.update', $book), $updatedData);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $book));

        $newGenres->each(function (Genre $genre) use ($book) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        });
    }

    // 他ユーザーの書籍に対する編集リクエストは、システム側で適切に 403 Forbidden の拒否レスポンスを返す
    public function test_other_user_cannot_edit_book_returns_403()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('books.update', $book), [
            'title' => '勝手に変更するタイトル',
            'author' => 'テスト著者',
            'isbn' => fake()->unique()->isbn13(),
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
    public function test_other_user_cannot_delete_book_returns_403()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $response->assertStatus(403);
    }

    /* =========================================================================
     * ■ ★応用要件：検索・フィルタ・ソート
     * ========================================================================= */

    // ページ遷移時に、検索条件（URLパラメータ）が維持（appends）されていること
    public function test_applied_search_parameters_are_appended_to_pagination_links()
    {
        $user = User::factory()->create();

        Book::factory()->count(20)->create([
            'title' => 'Laravel開発ガイド',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('books.index', [
            'keyword' => 'Laravel',
            'sort' => 'newest',
        ]));

        $response->assertStatus(200);
        $response->assertSee('keyword=Laravel');
        $response->assertSee('sort=newest');
    }

    // ソート（newest, oldest, title, rating）を指定した際、仕様通りの順序で書籍が正しく並び替わること（レビューなし書籍が最後になる処理を含む）。
    public function test_applied_books_are_sorted_correctly_including_rating_priority()
    {
        $user = User::factory()->create();

        $bookHigh = Book::factory()->create(['title' => 'A_Book', 'user_id' => $user->id]);
        $bookLow = Book::factory()->create(['title' => 'B_Book', 'user_id' => $user->id]);
        $bookNull = Book::factory()->create(['title' => 'C_Book', 'user_id' => $user->id]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookHigh->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $bookLow->id,
            'rating' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'rating']));

        $response->assertStatus(200);
        $response->assertSeeInOrder([$bookHigh->title, $bookLow->title, $bookNull->title]);
    }

    /* =========================================================================
     * ■ ★応用要件：書籍CRUD（認可の詳細テスト）
     * ========================================================================= */

    // 他ユーザーによるURL直打ち・不正アクセスの認可詳細テスト
    public function test_applied_other_user_cannot_access_or_modify_book_id_99_directly()
    {
        $owner = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'id' => 99,
            'user_id' => $owner->id,
            'title' => '絶対に書き換わってはいけないタイトル',
            'author' => '正規の著者',
            'isbn' => '9784000000000',
        ]);
        $book->genres()->attach($genre->id);

        $otherUser = User::factory()->create();

        // 検証1：編集画面へのURL直打ちブロック（GET /books/99/edit）
        $editResponse = $this->actingAs($otherUser)->get(route('books.edit', $book));
        $editResponse->assertStatus(403);

        // 検証2：直接のデータ更新（PUT /books/99）のブロック ＆ その後のデータ不変チェック
        $updateResponse = $this->actingAs($otherUser)->put(route('books.update', $book), [
            'title' => '改ざんを試みるタイトル',
            'author' => '攻撃者の名前',
            'isbn' => $book->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);
        $updateResponse->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => 99,
            'title' => '絶対に書き換わってはいけないタイトル',
            'author' => '正規の著者',
        ]);

        // 検証3：直接の削除リクエスト（DELETE /books/99）のブロック ＆ その後のデータ存在チェック
        $deleteResponse = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('books', ['id' => 99]);
    }
}
