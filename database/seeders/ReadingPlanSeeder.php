<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstBook = Book::first();

        if (! $firstBook) {
            return;
        }

        // 山田太郎 (主要シナリオ集約、ID: 1)
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today()->addDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today(),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today()->subDays(3),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today()->addDays(7),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today()->subDays(10),
            'status' => 'completed',
            'completed_at' => Carbon::today()->subDays(5),
        ]);

        // 鈴木花子 (他ユーザー認可テスト用、ID: 6)
        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => $firstBook->id,
            'target_date' => Carbon::today()->addDays(5),
            'status' => 'in_progress',
        ]);
    }
}
