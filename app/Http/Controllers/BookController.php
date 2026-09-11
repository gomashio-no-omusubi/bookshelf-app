<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreBookRequest;
use App\Http\Requests\Web\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::withAvg('reviews', 'rating')->latest()->paginate(10);

        return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        $book->loadAvg('reviews', 'rating')->load('genres');

        return view('books.show', compact('book'));
    }

    public function create()
    {
        $book = new Book;
        $genres = Genre::all();

        return view('books.create', compact('book', 'genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $book = Book::firstOrCreate(
            ['isbn' => $request->input('isbn')],
            [
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'published_date' => $request->input('published_date'),
                'description' => $request->input('description'),
                'img_url' => $request->input('img_url'),
                'condition_id' => $request->input('condition_id'),
                'user_id' => auth()->id(),
            ]
        );

        $book->genres()->sync($request->genres);

        return redirect()->route('books.index');
    }

    public function edit(Book $book)
    {
        $this->authorize('edit', $book);

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update([
            'title' => $request->input('title'),
            'author' => $request->input('author'),
            'published_date' => $request->input('published_date'),
            'description' => $request->input('description'),
            'img_url' => $request->input('img_url'),
            'condition_id' => $request->input('condition_id'),
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book);
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index');
    }
}
