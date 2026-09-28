<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationTest extends TestCase
{
    use RefreshDatabase;

    /* =========================================================================
     * User モデルのリレーションテスト
     * ========================================================================= */

    // Userは複数のBookを持つ（1対多）
    public function test_user_has_many_books()
    {
        $user = User::factory()->create();

        Book::factory()->create(['user_id' => $user->id]);

        $booksRelation = $user->books();
        $relatedBooks = $booksRelation->get();

        $this->assertInstanceOf(HasMany::class, $booksRelation);
        $this->assertCount(1, $relatedBooks);
    }

    // User は複数の Review を持つ（1対多）
    public function test_user_has_many_reviews()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $reviewsRelation = $user->reviews();
        $relatedReviews = $reviewsRelation->get();

        $this->assertInstanceOf(HasMany::class, $reviewsRelation);
        $this->assertCount(1, $relatedReviews);
    }

    // User は複数のお気に入り書籍を持つ（多対多：中間テーブル favorites）
    public function test_user_belongs_to_many_favorite_books()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $favoriteBooksRelation = $user->favoriteBooks();
        $favoriteBooksRelation->attach($book->id);

        $relatedBooks = $favoriteBooksRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $favoriteBooksRelation);
        $this->assertCount(1, $relatedBooks);
        $this->assertEquals($book->id, $relatedBooks->first()->id);
    }

    //  User は複数のいいねしたレビューを持つ（多対多：中間テーブル review_likes）
    public function test_user_belongs_to_many_liked_reviews()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $likedReviewsRelation = $user->likedReviews();
        $likedReviewsRelation->attach($review->id);

        $relatedReviews = $likedReviewsRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $likedReviewsRelation);
        $this->assertCount(1, $relatedReviews);
        $this->assertEquals($review->id, $relatedReviews->first()->id);
    }

    /* =========================================================================
     * Book モデルのリレーションテスト
     * ========================================================================= */

    // Book は一人の User に所属する（多対1）
    public function test_book_belongs_to_user()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $userRelation = $book->user();
        $relatedUser = $userRelation->first();

        $this->assertInstanceOf(BelongsTo::class, $userRelation);
        $this->assertEquals($user->id, $relatedUser->id);
    }

    // Book は複数の Review を持つ（1対多）
    public function test_book_has_many_reviews()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $reviewsRelation = $book->reviews();
        $relatedReviews = $reviewsRelation->get();

        $this->assertInstanceOf(HasMany::class, $reviewsRelation);
        $this->assertCount(1, $relatedReviews);
    }

    //  Book は複数の Genre を持つ（多対多：中間テーブル book_genre）
    public function test_user_belongs_to_many_genres()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $genresRelation = $book->genres();
        $genresRelation->attach($genre->id);

        $relatedGenres = $genresRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $genresRelation);
        $this->assertCount(1, $relatedGenres);
        $this->assertEquals($genre->id, $relatedGenres->first()->id);
    }

    // Book は複数の User からお気に入り登録される（多対多：中間テーブル favorites）
    public function test_book_is_favorited_by_many_users()
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $favoritesRelation = $book->favoritedByUsers();
        $favoritesRelation->attach($user->id);

        $relatedUsers = $favoritesRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $favoritesRelation);
        $this->assertCount(1, $relatedUsers);
        $this->assertEquals($user->id, $relatedUsers->first()->id);
    }

    //  Genre は複数の Book を持つ（多対多の逆方向検証）
    public function test_genre_belongs_to_many_books()
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $genresRelation = $book->genres();
        $genresRelation->attach($genre->id);

        $booksRelation = $genre->books();
        $relatedBooks = $booksRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $booksRelation);
        $this->assertCount(1, $relatedBooks);
        $this->assertEquals($book->id, $relatedBooks->first()->id);
    }

    /* =========================================================================
     * Review モデルのリレーションテスト
     * ========================================================================= */

    // Review は一人の User に所属する（多対1）
    public function test_review_belongs_to_user()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $userRelation = $review->user();
        $relatedUser = $userRelation->first();

        $this->assertInstanceOf(BelongsTo::class, $userRelation);
        $this->assertEquals($user->id, $relatedUser->id);
    }

    // Review は一つの Book に所属する（多対1）
    public function test_review_belongs_to_book()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $bookRelation = $review->book();
        $relatedBook = $bookRelation->first();

        $this->assertInstanceOf(BelongsTo::class, $bookRelation);
        $this->assertEquals($book->id, $relatedBook->id);
    }

    // Review は複数の User からいいねされる（多対多：中間テーブル review_likes）
    public function test_review_is_liked_by_many_users()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $likesRelation = $review->likedByUsers();
        $likesRelation->attach($user->id);

        $relatedUsers = $likesRelation->get();

        $this->assertInstanceOf(BelongsToMany::class, $likesRelation);
        $this->assertCount(1, $relatedUsers);
        $this->assertEquals($user->id, $relatedUsers->first()->id);
    }
}
