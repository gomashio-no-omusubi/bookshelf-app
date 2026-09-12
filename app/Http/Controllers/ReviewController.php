<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\StoreReviewRequest;
use App\Http\Requests\Web\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;

class ReviewController extends Controller
{
    public function toggle(Review $review)
    {
        auth()->user()->likedReviews()->toggle($review);

        return back();
    }

    public function store(StoreReviewRequest $request, Book $book)
    {
        if ($book->isReviewedBy(auth()->id())) {
            return back()->withErrors([
                'comment' => 'この書籍へのレビューはすでに投稿済みです。',
            ])->withInput();
        }
        $book->reviews()->create([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'user_id' => auth()->id(),
        ]);

        return back();
    }

    public function edit(Review $review)
    {
        $this->authorize('edit', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);

        $review->update([
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('books.show', $review->book_id);
    }

    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back();
    }
}
