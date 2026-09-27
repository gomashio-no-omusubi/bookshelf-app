<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * 公開API
     * ========================================================================= */

    // ==========================================
    // ■ 書籍一覧API
    // ==========================================

    // 1ページ当たり20件の書籍が正しく返却される。
    public function test_index_returns_paginated_books_with_20_items()
    {
        Book::factory()->count(25)->create();

        $response = $this->json('GET', route('api.v1.books.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
    }

    // キーワード（タイトル・著者名）やジャンルIDでの絞り込みが正常に機能する。
    public function test_index_filters_books_by_keyword_and_genre()
    {
        $genreA = Genre::factory()->create();
        $genreB = Genre::factory()->create();

        $book1 = Book::factory()->create(['title' => '夜行観覧車', 'author' => '湊かなえ']);
        $book2 = Book::factory()->create(['title' => 'OUT', 'author' => '桐野夏生']);

        $book1->genres()->attach($genreA->id);
        $book2->genres()->attach($genreB->id);

        $responseKeyword = $this->json('GET', route('api.v1.books.index'), ['keyword' => '観覧車']);
        $responseKeyword->assertStatus(200);
        $responseKeyword->assertJsonFragment(['id' => $book1->id]);
        $responseKeyword->assertJsonMissing(['id' => $book2->id]);

        $responseGenre = $this->json('GET', route('api.v1.books.index'), ['genre_id' => $genreB->id]);
        $responseGenre->assertStatus(200);
        $responseGenre->assertJsonFragment(['id' => $book2->id]);
        $responseGenre->assertJsonMissing(['id' => $book1->id]);
    }

    // ==========================================
    // ■ 書籍詳細API
    // ==========================================

    // 指定IDの書籍詳細を取得し、200レスポンスを返す。
    public function test_show_returns_book_details_and_200_status()
    {
        $book = Book::factory()->create([
            'title' => '夜行観覧車',
            'author' => '湊かなえ',
        ]);

        $response = $this->json('GET', route('api.v1.books.show', $book));

        $response->assertStatus(200);

        $response->assertJsonFragment(['id' => $book->id]);
    }

    // 存在しない書籍IDを指定してリクエストを送った際、自動的に 404 Not Found のエラーレスポンスを返す。
    public function test_show_returns_404_if_book_not_found()
    {
        $response = $this->json('GET', '/api/v1/books/99999');

        $response->assertStatus(404);
    }

    // ==========================================
    // ■ 書籍登録API
    // ==========================================

    // 登録された書籍のデータをすべて返し、201 Createdを返却する。
    public function test_store_creates_new_book_and_returns_201()
    {
        $genre = Genre::factory()->create();
        $user = User::factory()->create();

        $params = [
            'title' => 'リバース',
            'author' => '湊かなえ',
            'isbn' => '9784000000003',
            'published_date' => '2026-01-01',
            'description' => 'イヤミスの女王による傑作サスペンス。',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $response = $this->json('POST', route('api.v1.books.store'), $params);

        $response->assertStatus(201);

        $response->assertJsonFragment(['title' => 'リバース']);

        $this->assertDatabaseHas('books', ['isbn' => '9784000000003']);
    }

    // バリデーションエラーがある際は、422 Unprocessable Content となり、日本語エラーメッセージが返る。
    public function test_store_returns_422_with_japanese_errors_on_validation_failure()
    {
        $params = [
            'title' => '',
            'isbn' => '123',
        ];

        $response = $this->json('POST', route('api.v1.books.store'), $params);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title' => 'タイトルは必須です。',
            'isbn' => 'ISBNは13桁で入力してください。',
        ]);
    }

    // ==========================================
    // ■ 書籍更新API
    // ==========================================

    // 指定IDの書籍を更新し、200レスポンスを返す。（ISBNの一意性チェックで自身を除外できているか）
    public function test_update_modifies_existing_book_and_returns_200()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'OUT',
            'author' => '桐野夏生',
            'isbn' => '9784000000004',
            'user_id' => $user->id,
        ]);
        $genre = Genre::factory()->create();

        $params = [
            'title' => 'OUT（新装版）',
            'author' => '桐野夏生',
            'isbn' => '9784000000004',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $response = $this->json('PUT', route('api.v1.books.update', $book), $params);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'OUT（新装版）',
        ]);
    }

    // バリデーションエラーがある際は、422 Unprocessable Content となり、日本語エラーメッセージが返る。
    public function test_update_returns_422_on_validation_failure()
    {
        $book = Book::factory()->create();

        $params = ['title' => ''];

        $response = $this->json('PUT', route('api.v1.books.update', $book), $params);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'title' => 'タイトルは必須です。',
        ]);
    }

    // 存在しない書籍IDを指定してリクエストを送った際、自動的に 404 Not Found のエラーレスポンスを返す。
    public function test_update_returns_404_if_book_not_found()
    {
        $response = $this->json('PUT', '/api/v1/books/99999', ['title' => '変更']);

        $response->assertStatus(404);
    }

    // ==========================================
    // ■ 書籍削除API
    // ==========================================

    // 指定IDの書籍を削除し、 204 No Contentを返す。関連データ（レビュー・お気に入り・ジャンル紐付け）も適切に処理されること。
    public function test_destroy_deletes_book_and_returns_204()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this->json('DELETE', route('api.v1.books.destroy', $book));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);

        $this->assertDatabaseMissing('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    // 存在しない書籍IDを指定してリクエストを送った際、自動的に 404 Not Found のエラーレスポンスを返す。
    public function test_destroy_returns_404_if_book_not_found()
    {
        $response = $this->json('DELETE', '/api/v1/books/99999');

        $response->assertStatus(404);
    }
}
