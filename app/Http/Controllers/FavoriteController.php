<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * クラス FavoriteController
 *
 * 画面（Blade）向けのお気に入り書籍管理に関する画面表示および処理を行うコントローラーです。
 */
class FavoriteController extends Controller
{
    /**
     * 書籍のお気に入り状態を切り替えます（トグル処理）。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return RedirectResponse 前の画面へのリダイレクトレスポンス
     */
    public function toggle(Book $book): RedirectResponse
    {
        auth()->user()->favoriteBooks()->toggle($book);

        return back();
    }

    /**
     * お気に入り書籍の一覧画面を表示します。
     *
     * @return View 一覧画面のビューインスタンス
     */
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
