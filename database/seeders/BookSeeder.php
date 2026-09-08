<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $booksData = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'genres' => ['小説'],
                'description' => '猫の視点から人間の滑稽な生態を描いた、夏目漱石の不朽の名作ユーモア小説。',
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => 'あらゆる対人関係の基本であり、時代を超えて読み継がれる人間関係原則の最高峰。',
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'genres' => ['技術書'],
                'description' => '「美しいコードとは何か」を追求し、読みやすく直感的なコードを書くための実践的なバイブル。',
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '真の成功と幸福を手に入れるための普遍的な原則を体系化した、自己啓発書の決定版。',
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'genres' => ['小説'],
                'description' => '東京から四国の中学校に赴任した血気盛んな青年教師が、理不尽な社会に立ち向かう痛快劇。',
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'genres' => ['歴史', '科学'],
                'description' => 'ホモ・サピエンスがなぜ地球の支配者になれたのか、「虚構」を軸に人類の歴史を解き明かす。',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'genres' => ['技術書'],
                'description' => 'プロフェッショナルな開発者として、バグが少なくメンテナンスしやすい「綺麗なコード」の書き方を解説。',
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'genres' => ['自己啓発'],
                'description' => 'アドラー心理学の教えを青年と哲人の対話形式で説き、人間の悩みはすべて対人関係であると言い切る名著。',
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'genres' => ['小説'],
                'description' => '売れない芸人たちの葛藤と、お笑いという純粋な世界への情熱をリアルに描き芥川賞を受賞した傑作。',
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'genres' => ['ビジネス', '科学'],
                'description' => 'データや事実に基づき、思い込みや偏見に囚われずに世界を正しく見る方法を伝授するビジネス書。',
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'genres' => ['ビジネス', '歴史'],
                'description' => '「ただの鉄の箱」であるコンテナの普及が、世界の物流と経済の仕組みをいかに激変させたかを追ったノンフィクション。',
            ],
        ];

        $number = 1;

        foreach ($booksData as $data) {

            $book = Book::firstOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_at' => $data['published_at'],
                    'description' => $data['description'],
                    'image_url' => "https://placehold.co/200x300/e2e8f0/475569?text={$number}",
                ]
            );

            $genreIds = Genre::whereIn('name', $data['genres'])->pluck('id')->toArray();

            $book->genres()->sync($genreIds);

            $number++;
        }
    }
}
