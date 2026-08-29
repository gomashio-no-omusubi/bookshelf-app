<?php

namespace App\Http\Controllers;

class BookController extends Controller
{
    public function index()
    {
        return view('books.index');
    }

    public function show()
    {
        return view('books.show');
    }

    public function create()
    {
        return view('books.create');
    }

    public function edit()
    {
        return view('books.edit');
    }
}
