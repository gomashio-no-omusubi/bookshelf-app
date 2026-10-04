<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ReportService
{
    /**
     * 指定されたユーザーのレビューデータから、仕様に準拠した4種類の統計情報を一括で集計します。
     *
     * @param  mixed  $user  ログイン中のユーザーオブジェクト
     * @return Collection 集計済みの4種類の統計情報Collection
     */
    public function generateReport($user)
    {
        $reviews = $user->reviews()->with('book.genres')->get();

        // =============================================================
        // 1. 基本サマリー：総レビュー数、読了冊数（ユニーク数）、平均評価点
        // =============================================================
        $summary = collect([
            'total_reviews' => $reviews->count(),
            'books_read' => $reviews->unique('book_id')->count(),
            'average_rating' => round($reviews->avg('rating') ?? 0, 1),
        ]);

        // =============================================================
        // 2. 評価分布：1〜5星ごとの件数（グラフの横バー表示用）
        // =============================================================
        $ratingDistribution = collect([1, 2, 3, 4, 5])
            ->mapWithKeys(function ($star) use ($reviews) {
                return [$star => $reviews->where('rating', $star)->count()];
            });

        // =============================================================
        // 3. 高評価書籍TOP5：4星以上の書籍を評価の高い順に最大5件
        // =============================================================
        $topBooks = $reviews->filter(function ($review) {
            return $review->rating >= 4;
        })
            ->sortByDesc('rating')
            ->take(5)
            ->map(function ($review) {
                return [
                    'id' => $review->book_id,
                    'title' => $review->book ? $review->book->title : '不明',
                    'author' => $review->book ? $review->book->author : '不明',
                    'rating' => $review->rating,
                ];
            });

        // =============================================================
        // 4. ジャンル別評価傾向TOP5：ジャンルごとの平均評価と件数を高い順に最大5件
        // =============================================================
        $topGenres = $reviews->flatMap(function ($review) {
            return $review->book && $review->book->genres
                ? $review->book->genres->map(function ($genre) use ($review) {
                    return [
                        'id' => $genre->id,
                        'name' => $genre->name,
                        'rating' => $review->rating,
                    ];
                })
                : [];
        })
            ->groupBy('id')
            ->map(function ($genreGroup) {
                return [
                    'id' => $genreGroup->first()['id'],
                    'name' => $genreGroup->first()['name'],
                    'count' => $genreGroup->count(),
                    'average_rating' => round($genreGroup->avg('rating') ?? 0, 1),
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5);

        return collect([
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topBooks,
            'genre_ratings' => $topGenres,
        ]);
    }
}
