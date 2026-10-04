<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * アプリケーションの初期レビューデータをデータベースに投入します。
     * 各書籍に対してランダムに選ばれた複数ユーザーから2〜4件のレビューを生成し、
     * 評価値（1〜5）に応じた日本語コメントテンプレートを割り当てます。
     *
     * @param  void  引数はありません
     * @return void 戻り値はありません
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $templates = collect([
            5 => collect(['素晴らしい本でした！', '人生が変わりました。', '何度も読み返しています。']),
            4 => collect(['とても参考になりました。', '読みやすくておすすめです。', '期待通りの内容でした。']),
            3 => collect(['普通でした。', '可もなく不可もなく。', '期待したほどではなかった。']),
            2 => collect(['少し期待外れでした。', '内容が薄い印象。', 'もう少し深掘りしてほしかった。']),
            1 => collect(['残念ながら合いませんでした。', '期待と違いました。']),
        ]);

        $books->each(
            function (Book $book) use ($users, $templates) {

                $reviewCount = rand(2, 4);

                $reviewers = $users->random(min($reviewCount, $users->count()));

                $reviewers->each(function (User $reviewer) use ($book, $templates) {

                    $rating = rand(1, 5);

                    Review::create([
                        'book_id' => $book->id,
                        'user_id' => $reviewer->id,
                        'rating' => $rating,
                        'comment' => $templates->get($rating)->random(),
                    ]);
                });
            }
        );
    }
}
