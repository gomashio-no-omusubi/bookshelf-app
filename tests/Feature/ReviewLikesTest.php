<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikesTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * レビュー
     * ========================================================================= */

    // 認証ユーザーの場合、いいねを追加でき、review_likesにレコードが作成される。
    public function test_authenticated_user_can_add_book_to_review()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));

        $response->assertStatus(302);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // 認証ユーザーの場合、いいねを解除でき、review_likesテーブルからレコードが削除される。
    public function test_authenticated_user_can_remove_book_from_review()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);
        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // いいねのトグル（追加→解除→追加）が正しく動作する。
    public function test_reviews_like_toggle_mechanism_works_correctly()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));
        $response->assertStatus(302);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response = $this->actingAs($user)->post(route('reviews.like', $review));
        $response->assertStatus(302);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // 未認証（ゲスト）ユーザーが操作を試みた場合、処理を中断してログイン画面へ自動リダイレクトされる。
    public function test_guest_is_redirected_to_login_screen()
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $response = $this->post(route('reviews.like', $review));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }
}
