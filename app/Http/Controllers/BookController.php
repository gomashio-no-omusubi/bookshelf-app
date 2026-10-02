<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\IndexBookRequest;
use App\Http\Requests\Web\StoreBookRequest;
use App\Http\Requests\Web\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Services\GoogleBooksService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

/**
 * クラス BookController
 *
 * 画面（Blade）向けの書籍管理に関する画面表示および処理を行うコントローラーです。
 */
class BookController extends Controller
{
    /**
     * 書籍の一覧画面を表示します。（検索・フィルタ・ソート・ページネーション対応）
     *
     * @param  IndexBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return View 一覧画面のビューインスタンス
     */
    public function index(IndexBookRequest $request): View
    {
        $books = Book::searchAndSort($request->all())
            ->paginate(10)
            ->appends($request->query());

        $books->setCollection(
            $books->getCollection()->map(function (Book $book): Book {
                return $book;
            })
        );

        $genres = Genre::all();

        return view('books.index', [
            'books' => $books,
            'filters' => $request->all(),
            'genres' => $genres,
        ]);
    }

    /**
     * ISBNコードから書籍情報を非同期検索してJSONで返却します。
     *
     * @param  string  $isbn  13桁のISBNコード
     * @param  GoogleBooksService  $googleBooksService  外部API通信用サービス
     * @return JsonResponse 書籍情報のJSONレスポンス
     */
    public function searchByIsbn(string $isbn, GoogleBooksService $googleBooksService): JsonResponse
    {
        $result = $googleBooksService->fetchByIsbn($isbn);

        return response()->json($result);
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
