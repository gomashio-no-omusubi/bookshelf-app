<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * ランキング
     * ========================================================================= */

    // ランキングページ（/ranking）にアクセスした際、レビューが投稿されている書籍のみが抽出され、書籍タイトルが表示される。
    public function test_ranking_page_displays_successfully_with_reviewed_books()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'レビューのある書籍']);
        Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        $noReviewBook = Book::factory()->create(['title' => 'レビューのない書籍']);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee($book->title);
        $response->assertDontSee($noReviewBook->title);
    }

    // 各書籍の平均評価が高い順（降順）で正しくソートされる。
    public function test_books_are_ordered_by_average_rating_descending()
    {
        $user = User::factory()->create();

        $highRatingBook = Book::factory()->create(['title' => '高評価の書籍']);
        Review::factory()->create([
            'book_id' => $highRatingBook->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $lowRatingBook = Book::factory()->create(['title' => '低評価の書籍']);
        Review::factory()->create([
            'book_id' => $lowRatingBook->id,
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            $highRatingBook->title,
            $lowRatingBook->title,
        ]);
    }
}
