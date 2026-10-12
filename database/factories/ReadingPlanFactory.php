<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReadingPlan>
 */
class ReadingPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
            'completed_at' => null,
        ];
    }

    /**
     * テスト用：目標期限3日前の状態を作ります
     */
    public function threeDaysBefore(): static
    {
        return $this->state(fn () => [
            'target_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);
    }

    /**
     * テスト用：目標期限当日の状態を作ります
     */
    public function today(): static
    {
        return $this->state(fn () => [
            'target_date' => now()->format('Y-m-d'),
            'status' => ReadingPlanStatus::IN_PROGRESS,
        ]);
    }

    /**
     * テスト用：期限から3日が経過した期限切れ（expired）の状態を作ります
     */
    public function threeDaysAfterExpired(): static
    {
        return $this->state(fn () => [
            'target_date' => now()->subDays(3)->format('Y-m-d'),
            'status' => ReadingPlanStatus::EXPIRED,
        ]);
    }
}
