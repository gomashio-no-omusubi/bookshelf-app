<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;

use Illuminate\Support\Facades\Route;

// ==========================================
// 共通・一般公開ルート（未認証）
// ==========================================

// 書籍一覧（トップ）
Route::get('/', [BookController::class, 'index'])->name('books.index');
// 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
// ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

// ==========================================
// ログインユーザー限定ルート（要認証）
// ==========================================

Route::middleware(['auth'])->group(function () {

    // 書籍管理（登録・編集）
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');

    // ジャンル管理（一覧・登録・詳細・編集）
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');

    // レビュー編集
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
});
