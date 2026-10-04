<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 *お気に入りのテストデータを動的に投入するシーダークラスです。
 */
class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $user) {
            $favoriteBookIds = $books->pluck('id')->random(rand(3, 5))->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($favoriteBookIds);
        }
    }
}
