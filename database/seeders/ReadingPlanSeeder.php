<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * 読書計画のテストデータを動的に投入するシーダークラスです。
 */
class ReadingPlanSeeder extends Seeder
{
    /**
     * 読書計画のテストデータを生成します。
     */
    public function run(): void
    {
        $books = Book::limit(6)->get();

        if ($books->count() < 6) {
            $books = Book::factory()->count(6)->create();
        }

        // 山田太郎 (主要シナリオ集約、ID: 1)
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $books->get(0)->id,
            'target_date' => Carbon::today()->addDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $books->get(1)->id,
            'target_date' => Carbon::today(),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $books->get(2)->id,
            'target_date' => Carbon::today()->subDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $books->get(3)->id,
            'target_date' => Carbon::today()->addDays(7),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $books->get(4)->id,
            'target_date' => Carbon::today()->subDays(10),
            'status' => 'completed',
            'completed_at' => Carbon::today()->subDays(5),
        ]);

        // 鈴木花子 (他ユーザー認可テスト用、ID: 6)
        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => $books->get(5)->id,
            'target_date' => Carbon::today()->addDays(5),
            'status' => 'in_progress',
        ]);
    }
}
