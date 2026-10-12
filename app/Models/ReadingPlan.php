<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * クラス ReadingPlan
 *
 * 読書計画情報を管理するEloquentモデルです。
 */
class ReadingPlan extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル名
     *
     * @var string
     */
    protected $table = 'reading_plans';

    /**
     * 複数代入を許可する属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    /**
     * 属性のキャスト定義
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ReadingPlanStatus::class,
        'target_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 指定されたステータス（Enum）で読書計画を絞り込むスコープ
     * 引数に Enum型 を指定することで安全性を確保します。
     */
    public function scopeWithStatus(Builder $query, ?ReadingPlanStatus $status): Builder
    {
        if (is_null($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }
}
