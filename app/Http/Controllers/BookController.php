<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreBookRequest;
use App\Http\Requests\Web\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * クラス BookController
 *
 * 画面（Blade）向けの書籍管理に関する画面表示および処理を行うコントローラーです。
 */
class BookController extends Controller
{
    /**
     * 書籍の一覧画面を表示します。
     *
     * @return View 一覧画面のビューインスタンス
     */
    public function index(): View
    {
        $books = Book::withAvg('reviews', 'rating')->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍の詳細画面を表示します。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return View 詳細画面のビューインスタンス
     */
    public function show(Book $book): View
    {
        $book->loadAvg('reviews', 'rating')->load('genres');

        return view('books.show', compact('book'));
    }

    /**
     * 書籍の新規登録画面を表示します。
     *
     * @return View 新規登録画面のビューインスタンス
     */
    public function create(): View
    {
        $book = new Book;
        $genres = Genre::all();

        return view('books.create', compact('book', 'genres'));
    }

    /**
     * 新しい書籍をデータベースに登録します。
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return RedirectResponse 詳細画面へのリダイレクトレスポンス
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $book = Book::firstOrCreate(
            ['isbn' => $request->input('isbn')],
            [
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'image_url' => $request->input('image_url'),
                'user_id' => auth()->id(),
            ]
        );

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍の編集画面を表示します。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return View 編集画面のビューインスタンス
     */
    public function edit(Book $book): View
    {
        $this->authorize('edit', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍の情報を更新します。
     *
     * @param  UpdateBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  Book  $book  対象の書籍オブジェクト
     * @return RedirectResponse 詳細画面へのリダイレクトレスポンス
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $book->update([
            'title' => $request->input('title'),
            'author' => $request->input('author'),
            'isbn' => $request->input('isbn'),
            'published_date' => $request->input('published_date'),
            'description' => $request->input('description'),
            'image_url' => $request->input('image_url'),
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)->with('success', '書籍情報を更新しました。');
    }

    /**
     * 書籍をデータベースから削除します。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return RedirectResponse 一覧画面へのリダイレクトレスポンス
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍を削除しました。');
    }
}
