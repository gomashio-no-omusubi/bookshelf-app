<?php

namespace App\Models;

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

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'user_id',
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
}
