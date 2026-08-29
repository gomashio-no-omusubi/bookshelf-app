<?php

namespace App\Http\Controllers;

class FavoriteController extends Controller
{
    public function index()
    {
        return view('favorites.index');
    }
}
