<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * クラス BookController
 *
 * API（V1）向けの書籍管理に関するデータ制御およびレスポンス処理を行うコントローラーです。
 */
class BookController extends Controller
{
    /**
     * 書籍情報の一覧を条件に応じて取得します。
     *
     * @param  IndexBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return AnonymousResourceCollection 書籍リソースのコレクションレスポンス
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        if ($perPage > 100) {
            $perPage = 100;
        }
        $books = $query->latest()->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 新しい書籍情報をデータベースに登録します。
     *
     * @param  StoreBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @return JsonResponse HTTPステータスコード201を含むJSONレスポンス
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $book = Book::create(collect($validated)->except('genres')->toArray());

        $book->genres()->sync($request->input('genres'));

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 特定の書籍情報を取得します。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return BookResource 書籍リソースのインスタンス
     */
    public function show(Book $book): BookResource
    {
        $book->load(['genres', 'reviews'])
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 特定の書籍情報を更新します。
     *
     * @param  UpdateBookRequest  $request  バリデーション済みのリクエストオブジェクト
     * @param  Book  $book  対象の書籍オブジェクト
     * @return BookResource 書籍リソースのインスタンス
     */
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $validated = $request->validated();
        $book->update(collect($validated)->except('genres')->toArray());

        $book->genres()->sync($request->input('genres'));

        $book->load('genres')->loadAvg('reviews', 'rating')->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * 特定の書籍情報をデータベースから削除します。
     *
     * @param  Book  $book  対象の書籍オブジェクト
     * @return JsonResponse HTTPステータスコード204を含む空のJSONレスポンス
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->genres()->detach();

        $book->delete();

        return response()->json(null, 204);
    }
}
