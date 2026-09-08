<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($reviews as $review) {

            $likableUsers = $users->where('id', '!=', $review->user_id);

            $likeCount = rand(0, 3);

            if ($likeCount > 0 && $likableUsers->count() >= $likeCount) {
                $randomUserIds = $likableUsers->random($likeCount)->pluck('id')->toArray();

                $review->likedByUsers()->syncWithoutDetaching($randomUserIds);
            }
        }
    }
}
