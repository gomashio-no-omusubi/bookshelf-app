<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;

/**
 * クラス RankingController
 *
 * 画面（Blade）向けの書籍ランキング表示に関する画面表示および処理を行うコントローラーです。
 */
class RankingController extends Controller
{
    /**
     * レビュー評価に基づく書籍のランキング画面を表示します。
     *
     * @return View ランキング画面のビューインスタンス
     */
    public function index(): View
    {
        $rankedBooks = Book::has('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('reviews_avg_rating', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
