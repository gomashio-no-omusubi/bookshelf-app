<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * クラス Book
 *
 * 書籍情報を管理するEloquentモデルです。
 */
class Book extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
    ];

    /**
     * 属性のキャスト（文字列を自動的にCarbonオブジェクトに変換します）
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_date' => 'date',
    ];

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }

    public function readingPlans(): HasMany
    {
        return $this->hasMany(ReadingPlan::class);
    }

    /**
     * 指定されたユーザーが、この書籍に対して既にレビューを投稿しているか判定します。
     *
     * @param  int  $userId  判定対象のユーザーID
     * @return bool 既にレビューが存在する場合はtrue、存在しない場合はfalse
     */
    public function isReviewedBy(int $userId): bool
    {
        return $this->reviews()->where('user_id', $userId)->exists();
    }

    /**
     * 検索・フィルタ・ソートを統合して適用するローカルスコープ
     *
     * @param  Builder  $query  クエリビルダー
     * @param  array<string, mixed>  $params  リクエストから渡された検索パラメータ
     * @return Builder クエリビルダー
     */
    public function scopeSearchAndSort(Builder $query, array $params): Builder
    {
        return $query->withAvg('reviews', 'rating')
            ->where(function (Builder $q) use ($params) {
                if (! empty($params['keyword'])) {
                    $q->where('title', 'like', "%{$params['keyword']}%")
                        ->orWhere('author', 'like', "%{$params['keyword']}%");
                }
            })
            ->when(! empty($params['genre']), function (Builder $q) use ($params) {
                $q->whereHas('genres', function (Builder $g) use ($params) {
                    $g->where('book_genre.genre_id', $params['genre']);
                });
            })
            ->when($params['sort'] ?? 'latest', function (Builder $q, string $sort) {
                match ($sort) {
                    'oldest' => $q->orderBy('created_at', 'asc'),
                    'title' => $q->orderBy('title', 'asc'),
                    'rating' => $q->orderByRaw('reviews_avg_rating IS NULL ASC')
                        ->orderBy('reviews_avg_rating', 'desc'),
                    'newest', 'latest' => $q->orderBy('created_at', 'desc'),
                };
            });
    }
}
