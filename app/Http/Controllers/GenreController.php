<?php

namespace App\Http\Controllers;

class GenreController extends Controller
{
    public function index()
    {
        return view('genres.index');
    }

    public function create()
    {
        return view('genres.create');
    }

    public function show()
    {
        return view('genres.show');
    }

    public function edit()
    {
        return view('genres.edit');
    }
}
