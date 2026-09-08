<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            'とても読みやすくて、一気に読んでしまいました！',
            '実務に直結する知識が多く、非常に勉強になりました。',
            '初心者向けに丁寧に解説されていて分かりやすかったです。',
            '少し難しい部分もありましたが、学びが多かったです。',
            '図解が豊富で良かったです。何度も読み返します。',
        ];

        $reviewDistribution = [4, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2]; // 4件が1冊、3件が8冊、2件が2冊 ＝ 合計32件（すべて「2～4件」の範囲内）

        foreach ($books as $book) {

            $count = array_shift($reviewDistribution);

            $reviewers = $users->random(min($count ?? 0, $users->count()));

            foreach ($reviewers as $reviewer) {
                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $reviewer->id,
                    'rating' => rand(3, 5),
                    'comment' => collect($comments)->random(),
                ]);
            }
        }
    }
}
