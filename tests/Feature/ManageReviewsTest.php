<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageReviewsTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     *レビュー
     * ========================================================================= */

    // ログインユーザーのみがレビューを投稿でき、DB（reviewsテーブル）に正常に永続化される。
    public function test_authenticated_user_can_post_review()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $reviewData = [
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '素晴らしい本でした。',
        ];

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('reviews.store', $book), $reviewData);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
        ]);
    }

    // 評価（rating）が1〜5の有効範囲内であるかを判定するバリデーション機能を厳密にチェックする。
    public function test_review_rating_must_be_between_1_and_5()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $invalidData = [
            'book_id' => $book->id,
            'rating' => 6,
            'comment' => 'テスト',
        ];

        $response = $this->actingAs($user)->post(route('reviews.store', $book), $invalidData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['rating']);
    }

    // ゲストが投稿を試みた場合はログイン画面へリダイレクトされる。
    public function test_guest_cannot_post_review_and_redirects_to_login()
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book), [
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'ゲスト投稿テスト',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    // レビュー投稿者本人のみが自身のレビュー編集フォームの表示・更新できる。
    public function test_owner_can_view_edit_form_and_update_review()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)->get(route('reviews.edit', $review));
        $response->assertStatus(200);

        $updateData = [
            'rating' => 5,
            'comment' => '内容を更新しました。',
        ];

        $response = $this->actingAs($user)
            ->from(route('reviews.edit', $review))
            ->put(route('reviews.update', $review), $updateData);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
        ]);
    }

    // 他ユーザーのレビューに対する編集リクエストは、システム側で適切に 403 Forbidden の拒否レスポンスを返す。
    public function test_non_owner_cannot_view_edit_form_or_update_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get(route('reviews.edit', $review));
        $response->assertStatus(403);

        $this->actingAs($otherUser)
            ->put(route('reviews.update', $review), [
                'rating' => 1,
                'comment' => '勝手に変更するコメント',
            ])
            ->assertStatus(403);
    }

    // レビュー投稿者本人のみが削除でき、削除後にレビューが消えること。
    public function test_owner_can_delete_review()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->delete(route('reviews.destroy', $review));

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    // 他ユーザーのレビューに対する削除リクエストは、システム側で適切に 403 Forbidden の拒否レスポンスを返す。
    public function test_other_user_cannot_delete_review()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete(route('reviews.destroy', $review));

        $response->assertStatus(403);
    }
}
