<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Rental;
use Illuminate\Support\Carbon;

class RentService
{
    public function indexRent($rental)
    {
        return $rental = Rental::with(['book.author'])->paginate(3);
    }

    public function storeRent($validated)
    {
        $book = Book::findOrFail($validated['book_id']);

        if (!$book->isAvailable()) {
            return response()->json(['error' => 'Book not available'], 400);
        }

        $rental = Rental::create($validated);

        $book->decrement('available_copies');

        $rental->load(['book.author']);
        return $validated;
    }

    public function rentReturn($rental)
    {
        if ($rental->status !== 'active') {
            return response()->json(['error' => 'Rental already completed'], 400);
        }

        $rental->update([
            'return_date' => Carbon::today(),
            'status' => 'returned'
        ]);

        $rental->book->increment('available_copies');
        return $rental;
    }

    public function showRent($book)
    {
        $book->load(['author', 'rentals']);
    }
}
