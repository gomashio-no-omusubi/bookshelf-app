<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreReviewRequest;
use App\Http\Requests\Web\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * クラス ReviewController
 *
 * 画面（Blade）向けのレビュー管理に関する画面表示および処理を行うコントローラーです。
 */
class ReviewController extends Controller
{
    /**
     * レビューのいいね状態を切り替えます（トグル処理）。
     *
     * @param  Review  $review  対象のレビューオブジェクト
     * @return RedirectResponse 前の画面へのリダイレクトレスポンス
     */
    public function toggle(Review $review): RedirectResponse
    {
        auth()->user()->likedReviews()->toggle($review);

        return back();
    }

    /**
     * 書籍に対する新しいレビューをデータベースに登録します。
     *
     * @param  StoreReviewRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  Book  $book  レビュー対象の書籍オブジェクト
     * @return RedirectResponse 前の画面へのリダイレクトレスポンス
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        // 要件シートの指示（範囲検証）に基づき、FormRequestは入力値チェックに専念。
        // DBが絡む重複投稿の検知は、ビジネスロジックとしてコントローラーとモデルで制御。
        if ($book->isReviewedBy(auth()->id())) {
            return back()->withErrors([
                'comment' => 'この書籍へのレビューはすでに投稿済みです。',
            ])->withInput();
        }
        $book->reviews()->create([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビューの編集画面を表示します。
     *
     * @param  Review  $review  対象のレビューオブジェクト
     * @return View 編集画面のビューインスタンス
     */
    public function edit(Review $review): View
    {
        $this->authorize('edit', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューの情報を更新します。
     *
     * @param  UpdateReviewRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  Review  $review  対象のレビューオブジェクト
     * @return RedirectResponse 詳細画面へのリダイレクトレスポンス
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('books.show', $review->book_id)->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューをデータベースから削除します。
     *
     * @param  Review  $review  対象のレビューオブジェクト
     * @return RedirectResponse 前の画面へのリダイレクトレスポンス
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back()->with('success', 'レビューを削除しました。');
    }
}
