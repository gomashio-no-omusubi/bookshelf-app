<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreGenreRequest;
use App\Http\Requests\Web\UpdateGenreRequest;
use App\Models\Book;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')->orderBy('id', 'asc')->get();

        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        $genre = new Genre;

        return view('genres.create', compact('genre'));
    }

    public function store(StoreGenreRequest $request)
    {
        Genre::firstOrCreate(['name' => $request->input('name')]);

        return redirect()->route('genres.index');
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function edit(Genre $genre)
    {
        $books = Book::all();

        return view('genres.edit', compact('genre', 'books'));
    }

    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $oldName = $genre->name;

        $genre->update(['name' => $request->input('name')]);

        return redirect()->route('genres.index', $genre);
    }

    public function destroy(Genre $genre)
    {
        if ($genre->books()->exists()) {

            return back();
        }

        $name = $genre->name;
        $genre->delete();

        return back();
    }
}
