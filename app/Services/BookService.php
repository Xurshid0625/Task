<?php

namespace App\Services;

use App\Models\Book;

class BookService
{
    public function createBook($validated)
    {
        $validated['available_copies'] = $validated['total_copies'];

        $book = Book::create($validated);

        $book->load('author');

        return $validated;
    }

    public function updateBook(Book $book, $validated)
    {
        $difference = $validated['total_copies'] - $book->total_copies;
        $validated['available_copies'] = $book->available_copies + $difference;

        $book->update($validated);

        $book->load('author');

        return $book;
    }

    public function showBook($book)
    {
        return $book->load(['author', 'rentals']);
    }

    public function deleteBook($book)
    {
        if ($book->rentals()->active()->exists()) {
            return response()->json(['error' => 'Cannot delete book with active rentals'], 400);
        }

        $book->delete();
    }
}
