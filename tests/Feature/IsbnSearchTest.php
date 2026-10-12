<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ■ ★応用要件：ISBN検索
     * ========================================================================= */

    // 正しいISBNでリクエストした際、Google Books APIの仕様に準拠した書籍データのJSONが返ること
    public function test_isbn_search_returns_correct_json_on_success()
    {
        $user = User::factory()->create();

        Http::fake([
            '*' => Http::response([
                'items' => [
                    'volumeInfo' => [
                        'title' => '極限のLaravel開発',
                        'authors' => ['テスト著者'],
                        'publishedDate' => '2026-05-01',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('books.isbn.search', ['isbn' => '9784000000000']));

        $response->assertStatus(200);
        $response->assertJson([
            'title' => '極限のLaravel開発',
            'author' => 'テスト著者',
            'published_date' => '2026-05-01',
        ]);
    }

    // 通信失敗時、およびシステム例外発生時に、クラッシュせず適切なエラーメッセージのJSONが返ること
    public function test_isbn_search_handles_errors_gracefully()
    {
        $user = User::factory()->create();

        // パターンA：通信失敗（外部APIが500エラー）
        Http::fake([
            '*' => Http::response(['error' => 'Server Error'], 500),
        ]);

        $response = $this->actingAs($user)->get(route('books.isbn.search', ['isbn' => '9784000000000']));

        $response->assertStatus(200);
        $response->assertJson([
            'error' => 'API通信エラーが発生しました。',
        ]);
    }

    // 書籍未存在（該当なし）時、クラッシュせず「書籍が見つかりませんでした。」という適切なエラーメッセージのJSONが返ること
    public function test_isbn_search_returns_not_found_error_when_book_does_not_exist()
    {
        $user = User::factory()->create();

        // 【要件(2)の検証】：該当なし（totalItems = 0）のレスポンスをモック
        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get('/books/isbn/9784000000001');

        $response->assertStatus(200);
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }
}
