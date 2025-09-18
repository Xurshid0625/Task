<?php

namespace App\Models\Report;

use App\Models\Book;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RentalStatistic extends Model
{
    protected $fillable = [
        'date',
        'year',
        'month',
        'day',
        'new_rentals',
        'returned_books',
        'overdue_books',
        'active_rentals_end_of_day',
        'utilization_rate',
    ];

    protected $casts = [
        'date' => 'date',
        'utilization_rate' => 'decimal:2',
    ];

    public static function generateDailyStats($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        $newRentals = Rental::whereDate('rental_date', $date)->count();
        $returnedBooks = Rental::whereDate('return_date', $date)->count();
        $overdueBooks = Rental::where('status', 'active')
            ->where('due_date', '<', $date)
            ->count();

        $activeRentals = Rental::where('status', 'active')
            ->where('rental_date', '<=', $date)
            ->count();

        $totalBooks = Book::sum('total_copies');
        $utilizationRate = $totalBooks > 0
            ? (($totalBooks - Book::sum('available_copies')) / $totalBooks) * 100
            : 0;

        return self::updateOrCreate(
            ['date' => $date->toDateString()],
            [
                'year' => $date->year,
                'month' => $date->month,
                'day' => $date->day,
                'new_rentals' => $newRentals,
                'returned_books' => $returnedBooks,
                'overdue_books' => $overdueBooks,
                'active_rentals_end_of_day' => $activeRentals,
                'utilization_rate' => round($utilizationRate, 2),
            ]
        );
    }

    public static function getMonthlySummary($year, $month)
    {
        return self::where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                SUM(new_rentals) as total_new_rentals,
                SUM(returned_books) as total_returned_books,
                AVG(active_rentals_end_of_day) as avg_active_rentals,
                AVG(utilization_rate) as avg_utilization_rate,
                MAX(overdue_books) as max_overdue_books
            ')
            ->first();
    }
}
