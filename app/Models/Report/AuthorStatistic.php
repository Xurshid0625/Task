<?php

namespace App\Models\Report;

use App\Models\Author;
use Illuminate\Database\Eloquent\Model;

class AuthorStatistic extends Model
{
    protected $fillable = [
        'author_id',
        'total_books',
        'total_book_copies',
        'total_rentals',
        'total_returns',
        'current_active_rentals',
        'average_rentals_per_book',
        'popularity_score',
        'last_rental_date',
    ];

    protected $casts = [
        'last_rental_date' => 'date',
        'average_rentals_per_book' => 'decimal:2',
        'popularity_score' => 'decimal:2',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function updateStatistics()
    {
        $author = $this->author->load('books.rentals');

        $this->total_books = $author->books->count();
        $this->total_book_copies = $author->books->sum('total_copies');

        $allRentals = collect();
        foreach ($author->books as $book) {
            $allRentals = $allRentals->merge($book->rentals);
        }

        $this->total_rentals = $allRentals->count();
        $this->total_returns = $allRentals->where('status', 'returned')->count();
        $this->current_active_rentals = $allRentals->where('status', 'active')->count();

        $this->average_rentals_per_book = $this->total_books > 0
            ? $this->total_rentals / $this->total_books
            : 0;

        $this->last_rental_date = $allRentals->max('rental_date');
        $this->popularity_score = $this->calculatePopularityScore();

        $this->save();
    }

    private function calculatePopularityScore()
    {
        return $this->total_rentals * 5 + $this->total_books * 10;
    }
}
