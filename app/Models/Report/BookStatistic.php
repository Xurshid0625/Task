<?php

namespace App\Models\Report;

use App\Models\Book;
use Illuminate\Database\Eloquent\Model;

class BookStatistic extends Model
{
    protected $fillable = [
        'book_id',
        'total_rentals',
        'total_returns',
        'current_active_rentals',
        'overdue_count',
        'average_rental_duration',
        'popularity_score',
        'last_rented_date',
        'last_returned_date',
    ];

    protected $casts = [
        'last_rented_date' => 'date',
        'last_returned_date' => 'date',
        'average_rental_duration' => 'decimal:2',
        'popularity_score' => 'decimal:2',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function calculatePopularityScore()
    {
        $baseScore = $this->total_rentals * 10;

        if ($this->last_rented_date) {
            $daysSinceLastRental = now()->diffInDays($this->last_rented_date);
            $recencyBoost = max(0, (30 - $daysSinceLastRental) * 2);
            $baseScore += $recencyBoost;
        }

        $returnRate = $this->total_rentals > 0 ? ($this->total_returns / $this->total_rentals) : 0;
        $baseScore += $returnRate * 50;

        return round($baseScore, 2);
    }


    public function updateStatistics()
    {
        $book = $this->book;
        $rentals = $book->rentals;

        $this->total_rentals = $rentals->count();
        $this->total_returns = $rentals->where('status', 'returned')->count();
        $this->current_active_rentals = $rentals->where('status', 'active')->count();
        $this->overdue_count = $rentals->where('status', 'active')
            ->where('due_date', '<', now()->toDateString())->count();

        // Calculate average rental duration for returned books
        $returnedRentals = $rentals->whereNotNull('return_date');
        if ($returnedRentals->count() > 0) {
            $totalDuration = $returnedRentals->sum(function ($rental) {
                return $rental->rental_date->diffInDays($rental->return_date);
            });
            $this->average_rental_duration = $totalDuration / $returnedRentals->count();
        }

        $this->last_rented_date = $rentals->max('rental_date');
        $this->last_returned_date = $rentals->whereNotNull('return_date')->max('return_date');
        $this->popularity_score = $this->calculatePopularityScore();

        $this->save();
    }
}
