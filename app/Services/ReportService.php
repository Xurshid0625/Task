<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Book;
use App\Models\Rental;
use Carbon\Carbon;

class ReportService
{
    public function CurrentStatistics($stats)
    {
        $stats = [
            'total_books' => Book::count(),
            'total_authors' => Author::count(),
            'total_book_copies' => Book::sum('total_copies'),
            'available_books' => Book::sum('available_copies'),
            'rented_books' => Book::sum('total_copies') - Book::sum('available_copies'),
            'active_rentals' => Rental::active()->count(),
            'overdue_rentals' => Rental::overdue()->count(),
            'total_rentals_ever' => Rental::count(),
            'returned_books' => Rental::returned()->count(),
        ];

        $stats['utilization_rate'] = $stats['total_book_copies'] > 0
            ? round(($stats['rented_books'] / $stats['total_book_copies']) * 100, 2)
            : 0;

        $stats['return_rate'] = $stats['total_rentals_ever'] > 0
            ? round(($stats['returned_books'] / $stats['total_rentals_ever']) * 100, 2)
            : 0;

        return $stats;
    }

    public function MostRentedBooks($request, Book $book)
    {
        $limit = $request->get('limit', 2);

        $book = Book::withCount('rentals')
            ->with(['author:id,name'])
            ->orderBy('rentals_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author_name' => $book->author->name,
                    'isbn' => $book->isbn,
                    'total_copies' => $book->total_copies,
                    'available_copies' => $book->available_copies,
                    'rental_count' => $book->rentals_count,
                    'utilization_rate' => $book->total_copies > 0
                        ? round((($book->total_copies - $book->available_copies) / $book->total_copies) * 100, 2)
                        : 0
                ];
            });
        return $book;
    }

    public function AuthorStatistics($request, $author)
    {
        $limit = $request->get('limit', 2);

        $authors = Author::with(['books' => function ($query) {
            $query->withCount('rentals');
        }])
            ->get()
            ->map(function ($author) {
                $totalRentals = $author->books->sum('rentals_count');
                $totalBooks = $author->books->count();
                $totalCopies = $author->books->sum('total_copies');
                $availableCopies = $author->books->sum('available_copies');

                return [
                    'id' => $author->id,
                    'name' => $author->name,
                    'nationality' => $author->nationality,
                    'total_books' => $totalBooks,
                    'total_copies' => $totalCopies,
                    'available_copies' => $availableCopies,
                    'rented_copies' => $totalCopies - $availableCopies,
                    'total_rentals' => $totalRentals,
                    'average_rentals_per_book' => $totalBooks > 0
                        ? round($totalRentals / $totalBooks, 2)
                        : 0,
                    'popularity_score' => $totalRentals,
                ];
            })
            ->sortByDesc('total_rentals')
            ->take($limit)
            ->values();

        return $authors;
    }

    public function OverdueReport()
    {
        $overdueRentals = Rental::overdue()
            ->with(['book.author:id,name', 'book:id,title,author_id'])
            ->get()
            ->map(function ($rental) {
                $daysOverdue = Carbon::parse($rental->due_date)->diffInDays(Carbon::now());

                return [
                    'rental_id' => $rental->id,
                    'book_title' => $rental->book->title,
                    'author_name' => $rental->book->author->name,
                    'renter_name' => $rental->renter_name,
                    'renter_email' => $rental->renter_email,
                    'renter_phone' => $rental->renter_phone,
                    'rental_date' => $rental->rental_date->format('Y-m-d'),
                    'due_date' => $rental->due_date->format('Y-m-d'),
                    'days_overdue' => $daysOverdue,
                    'overdue_category' => $this->getOverdueCategory($daysOverdue)
                ];
            })
            ->sortByDesc('days_overdue')
            ->values();

        return $overdueRentals;
    }

    public function ActiveRentals()
    {
        $activeRentals = Rental::active()
            ->with(['book.author:id,name', 'book:id,title,author_id'])
            ->get()
            ->map(function ($rental) {
                $daysUntilDue = Carbon::now()->diffInDays(Carbon::parse($rental->due_date), false);

                return [
                    'rental_id' => $rental->id,
                    'book_title' => $rental->book->title,
                    'author_name' => $rental->book->author->name,
                    'renter_name' => $rental->renter_name,
                    'renter_email' => $rental->renter_email,
                    'rental_date' => $rental->rental_date->format('Y-m-d'),
                    'due_date' => $rental->due_date->format('Y-m-d'),
                    'days_until_due' => $daysUntilDue,
                    'status_category' => $this->getDueCategory($daysUntilDue)
                ];
            })
            ->sortBy('days_until_due')
            ->values();

        return $activeRentals;
    }

    public function BooksAvailability()
    {
        $books = Book::with('author:id,name')
            ->get()
            ->map(function ($book) {
                $utilizationRate = $book->total_copies > 0
                    ? round((($book->total_copies - $book->available_copies) / $book->total_copies) * 100, 2)
                    : 0;

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author_name' => $book->author->name,
                    'total_copies' => $book->total_copies,
                    'available_copies' => $book->available_copies,
                    'rented_copies' => $book->total_copies - $book->available_copies,
                    'utilization_rate' => $utilizationRate,
                    'availability_status' => $this->getAvailabilityStatus($book->available_copies, $book->total_copies)
                ];
            })
            ->sortByDesc('utilization_rate')
            ->values();

        return $books;
    }


    private function getOverdueCategory($daysOverdue)
    {
        if ($daysOverdue <= 7) return 'recently_overdue';
        if ($daysOverdue <= 30) return 'moderately_overdue';
        return 'severely_overdue';
    }

    private function getDueCategory($daysUntilDue)
    {
        if ($daysUntilDue < 0) return 'overdue';
        if ($daysUntilDue <= 3) return 'due_soon';
        if ($daysUntilDue <= 7) return 'due_this_week';
        return 'due_later';
    }

    private function getAvailabilityStatus($available, $total)
    {
        if ($available == 0) return 'out_of_stock';
        if ($available == $total) return 'fully_available';
        return 'partially_available';
    }
}
