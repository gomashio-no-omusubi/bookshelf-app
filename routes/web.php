<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 共通・一般公開ルート（未認証）
// ==========================================

// 書籍一覧（トップ）
Route::get('/', [BookController::class, 'index'])->name('books.index');
// ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ==========================================
// ログインユーザー限定ルート（要認証）
// ==========================================

Route::middleware(['auth'])->group(function () {

    // 書籍管理（登録・編集・更新・削除）
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/', [BookController::class, 'store'])->name('books.store');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}/edit', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // ジャンル管理（一覧・登録・詳細・編集）
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');

    // レビュー管理
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

    // お気に入り管理
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ==========================================
// 例外：衝突を避けるために最下部に配置（未認証OK）
// ==========================================
// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
