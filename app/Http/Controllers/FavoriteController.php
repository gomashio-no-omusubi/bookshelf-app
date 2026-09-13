<?php

namespace App\Http\Controllers;

use App\Models\Book;

class FavoriteController extends Controller
{
    public function toggle(Book $book)
    {
        auth()->user()->favoriteBooks()->toggle($book);

        return back();
    }

    public function index()
    {
        $books = auth()->user()->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
