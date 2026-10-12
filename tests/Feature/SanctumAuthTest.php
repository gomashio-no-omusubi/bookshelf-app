<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;
    /* =========================================================================
     * ■ ★応用要件：Sanctum認証
     * ========================================================================= */

    // 認可エラー（403 Forbidden）キーは "error"、文言は「この操作を実行する権限がありません。」となること
    public function test_sanctum_api_returns_correct_403_forbidden_response()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson(route('api.v1.books.destroy', $book->id));

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);
    }

    // 存在しないIDの指定（404 Not Found）キーは "error"、文言は「書籍が見つかりませんでした。」となること
    public function test_sanctum_api_returns_correct_404_not_found_response()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/books/99999');

        $response->assertStatus(404);
        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }

    // 未認証エラー（401 Unauthorized）キーは "message"、文言は英語の「Unauthenticated.」となること
    public function test_sanctum_api_returns_correct_401_unauthorized_response()
    {
        $response = $this->postJson(route('api.v1.books.store'), ['title' => '未認証テスト']);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
