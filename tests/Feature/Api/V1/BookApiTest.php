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

    //  1ページ当たり20件の書籍が正しく返却される構造（data + meta）を検証する。
    public function test_api_index_returns_paginated_books_with_20_items()
    {
        Book::factory()->count(25)->create();

        $url = route('api.v1.books.index');
        $response = $this->getJson($url);

        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');

        $expectedStructure = [
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'user_id',
                    'genres',
                    'reviews_avg_rating',
                    'reviews_count',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ];
        $response->assertJsonStructure($expectedStructure);
    }

    // キーワード（タイトル・著者名）やジャンルIDでの絞り込みが正常に機能する。
    public function test_api_index_filters_books_by_keyword_and_genre()
    {
        $genreA = Genre::factory()->create();
        $genreB = Genre::factory()->create();

        $book1 = Book::factory()->create(['title' => '夜行観覧車', 'author' => '湊かなえ']);
        $book2 = Book::factory()->create(['title' => 'OUT', 'author' => '桐野夏生']);

        $book1GenresRelation = $book1->genres();
        $book1GenresRelation->attach($genreA->id);

        $book2GenresRelation = $book2->genres();
        $book2GenresRelation->attach($genreB->id);

        $keywordParams = ['keyword' => '観覧車'];
        $keywordUrl = route('api.v1.books.index', $keywordParams);
        $responseKeyword = $this->getJson($keywordUrl);

        $responseKeyword->assertStatus(200);
        $responseKeyword->assertJsonFragment(['id' => $book1->id]);
        $responseKeyword->assertJsonMissing(['id' => $book2->id]);

        $genreParams = ['genre_id' => $genreB->id];
        $genreUrl = route('api.v1.books.index', $genreParams);
        $responseGenre = $this->getJson($genreUrl);

        $responseGenre->assertStatus(200);
        $responseGenre->assertJsonFragment(['id' => $book2->id]);
        $responseGenre->assertJsonMissing(['id' => $book1->id]);
    }

    // ==========================================
    // ■ 書籍詳細API
    // ==========================================

    // 指定IDの書籍詳細を構造検証（dataの中にジャンルやレビューが含まれること）を含めて取得し、200を返す。
    public function test_api_show_returns_book_details_and_200_status()
    {
        $book = Book::factory()->create([
            'title' => '夜行観覧車',
            'author' => '湊かなえ',
        ]);

        $url = route('api.v1.books.show', $book);
        $response = $this->getJson($url);

        $response->assertStatus(200);

        $expectedStructure = [
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'user_id',
                'genres',
                'reviews_avg_rating',
                'reviews_count',
                'reviews',
                'created_at',
                'updated_at',
            ],
        ];
        $response->assertJsonStructure($expectedStructure);
    }

    // 存在しない書籍IDを指定した際、404 Not Found とエラーJSONを返す。
    public function test_api_show_returns_404_if_book_not_found()
    {
        $url = '/api/v1/books/99999';
        $response = $this->getJson($url);

        $response->assertStatus(404);

        $expectedErrorStructure = [
            'message',
        ];
        $response->assertJsonStructure($expectedErrorStructure);
    }

    // ==========================================
    // ■ 書籍登録API
    // ==========================================

    // 正しいパラメータで書籍とジャンルがDBに登録され、201 Createdが返却される。
    public function test_api_store_creates_new_book_and_returns_201()
    {
        $genre = Genre::factory()->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $params = [
            'title' => 'リバース',
            'author' => '湊かなえ',
            'isbn' => '9784000000003',
            'published_date' => '2026-01-01',
            'description' => 'イヤミスの女王による傑作サスペンス。',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $url = route('api.v1.books.store');
        $response = $this->postJson($url, $params);

        $response->assertStatus(201);

        $expectedBookInDb = [
            'title' => 'リバース',
            'author' => '湊かなえ',
            'isbn' => '9784000000003',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ];
        $this->assertDatabaseHas('books', $expectedBookInDb);

        $createdBookId = $response->json('data.id');

        $expectedPivotInDb = [
            'book_id' => $createdBookId,
            'genre_id' => $genre->id,
        ];
        $this->assertDatabaseHas('book_genre', $expectedPivotInDb);
    }

    // バリデーションエラーがある際は、422 Unprocessable Content となりエラーメッセージが返る。
    public function test_api_store_returns_422_on_validation_failure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $params = [
            'title' => '',
            'isbn' => '123',
        ];

        $url = route('api.v1.books.store');
        $response = $this->postJson($url, $params);

        $response->assertStatus(422);

        $expectedValidationKeys = [
            'title',
            'isbn',
        ];
        $response->assertJsonValidationErrors($expectedValidationKeys);
    }

    // ==========================================
    // ■ 書籍更新API
    // ==========================================

    // 指定IDの書籍情報が正しく更新、DBに反映され200を返す。
    public function test_api_update_modifies_existing_book_and_returns_200()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'title' => 'OUT',
            'author' => '桐野夏生',
            'isbn' => '9784000000004',
            'user_id' => $user->id,
        ]);
        $genre = Genre::factory()->create();

        $this->actingAs($user);

        $params = [
            'title' => 'OUT（新装版）',
            'author' => '桐野夏生',
            'isbn' => '9784000000004',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ];

        $url = route('api.v1.books.update', $book);
        $response = $this->putJson($url, $params);

        $response->assertStatus(200);

        $expectedBookInDb = [
            'id' => $book->id,
            'title' => 'OUT（新装版）',
            'isbn' => '9784000000004',
        ];

        $this->assertDatabaseHas('books', $expectedBookInDb);
    }

    // バリデーションエラーがある際は、422 Unprocessable Content となりエラーメッセージが返る。
    public function test_api_update_returns_422_on_validation_failure()
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $this->actingAs($user);

        $params = ['title' => ''];

        $url = route('api.v1.books.update', $book);
        $response = $this->putJson($url, $params);

        $response->assertStatus(422);

        $expectedValidationKeys = [
            'title',
        ];
        $response->assertJsonValidationErrors($expectedValidationKeys);
    }

    // 存在しない書籍IDを指定した際、404 Not Found とエラーJSONを返す。
    public function test_api_update_returns_404_if_book_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = '/api/v1/books/99999';
        $params = ['title' => '変更'];

        $response = $this->putJson($url, $params);

        $response->assertStatus(404);

        $expectedErrorStructure = [
            'message',
        ];
        $response->assertJsonStructure($expectedErrorStructure);
    }

    // ==========================================
    // ■ 書籍削除API
    // ==========================================

    // 指定IDの書籍を削除し、 204 No Contentを返す。関連データ（レビュー・お気に入り・ジャンル紐付け）も適切に処理されること。
    public function test_api_destroy_deletes_book_and_returns_204()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $bookGenresRelation = $book->genres();
        $bookGenresRelation->attach($genre->id);

        $url = route('api.v1.books.destroy', $book);
        $response = $this->deleteJson($url);

        $response->assertStatus(204);

        $expectedMissingBook = [
            'id' => $book->id,
        ];
        $this->assertDatabaseMissing('books', $expectedMissingBook);

        $expectedMissingPivot = [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ];
        $this->assertDatabaseMissing('book_genre', $expectedMissingPivot);
    }

    // 存在しない書籍IDを指定した際、404 Not Found とエラーJSONを返す。
    public function test_api_destroy_returns_404_if_book_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $url = '/api/v1/books/99999';
        $response = $this->deleteJson($url);

        $response->assertStatus(404);

        $expectedErrorStructure = [
            'message',
        ];
        $response->assertJsonStructure($expectedErrorStructure);
    }
}
