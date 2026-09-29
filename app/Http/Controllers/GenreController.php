<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreGenreRequest;
use App\Http\Requests\Web\UpdateGenreRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * クラス GenreController
 *
 * 画面（Blade）向けのジャンル管理に関する画面表示および処理を行うコントローラーです。
 */
class GenreController extends Controller
{
    /**
     * ジャンル一覧画面を表示します。
     *
     * @return View 一覧画面のビューインスタンス
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->orderBy('id', 'asc')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録画面を表示します。
     *
     * @return View 登録画面のビューインスタンス
     */
    public function create(): View
    {
        $genre = new Genre;

        return view('genres.create', compact('genre'));
    }

    /**
     * 新しいジャンルをデータベースに登録します。
     *
     * @param  StoreGenreRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return RedirectResponse 一覧画面へのリダイレクトレスポンス
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::firstOrCreate(['name' => $request->input('name')]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを作成しました。');
    }

    /**
     * ジャンルの詳細画面を表示します。
     *
     * @param  Genre  $genre  対象のジャンルオブジェクト
     * @return View 詳細画面のビューインスタンス
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンルの編集画面を表示します。
     *
     * @param  Genre  $genre  対象のジャンルオブジェクト
     * @return View 編集画面のビューインスタンス
     */
    public function edit(Genre $genre): View
    {
        $books = Book::all();

        return view('genres.edit', compact('genre', 'books'));
    }

    /**
     * ジャンルの情報を更新します。
     *
     * @param  UpdateGenreRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  Genre  $genre  対象のジャンルオブジェクト
     * @return RedirectResponse 一覧画面へのリダイレクトレスポンス
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update(['name' => $request->input('name')]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました。');
    }

    /**
     * ジャンルをデータベースから削除します（書籍の紐付けがある場合はブロック）。
     *
     * @param  Genre  $genre  対象のジャンルオブジェクト
     * @return RedirectResponse 前の画面へのリダイレクトレスポンス
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        if ($genre->books()->exists()) {

            return back()->with('error', 'このジャンルには書籍が紐付いているため削除できません。');
        }

        $genre->delete();

        return back()->with('success', 'ジャンルを削除しました。');
    }
}
