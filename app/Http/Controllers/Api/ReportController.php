<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Rental;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request, Book $book, State $stats, Author $author)
    {
        return response()->json([
            'current_statistics' => $this->getCurrentStatistics($request, $stats),
            'most_rented_books' => $this->getMostRentedBooks($request, $book),
            'author_statistics' => $this->getAuthorStatistics($request, $author),
            'rental_trends' => $this->getRentalTrends($request),
        ]);
    }

    public function getCurrentStatistics(Request $request, State $stats)
    {
        $stats = $this->reportService->CurrentStatistics($stats);
        return $this->statsResponses($stats, 'Success', 200);
    }

    public function getMostRentedBooks(Request $request, Book $book)
    {
        $book = $this->reportService->MostRentedBooks($request, $book);
        return $this->bookResponses($book, 'Success', 200);
    }

    public function getAuthorStatistics(Request $request, Author $author)
    {
        $author = $this->reportService->AuthorStatistics($request, $author);
        return $this->authorResponse($author, 'Success', 200);
    }

    public function getRentalTrends(Request $request)
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 12);

        $format = match ($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m'
        };

        $trends = Rental::select(
            DB::raw("DATE_FORMAT(rental_date, '$format') as period"),
            DB::raw('COUNT(*) as rental_count'),
            DB::raw('COUNT(CASE WHEN status = "returned" THEN 1 END) as returned_count'),
            DB::raw('COUNT(CASE WHEN status = "active" THEN 1 END) as active_count'),
            DB::raw('COUNT(CASE WHEN status = "overdue" OR (status = "active" AND due_date < CURDATE()) THEN 1 END) as overdue_count')
        )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($trend) use ($period) {
                return [
                    'period' => $trend->period,
                    'period_formatted' => $this->formatPeriod($trend->period, $period),
                    'rental_count' => $trend->rental_count,
                    'returned_count' => $trend->returned_count,
                    'active_count' => $trend->active_count,
                    'overdue_count' => $trend->overdue_count,
                    'return_rate' => $trend->rental_count > 0
                        ? round(($trend->returned_count / $trend->rental_count) * 100, 2)
                        : 0
                ];
            });

        return response()->json($trends->reverse()->values());
    }

    public function getOverdueReport()
    {
        $overdueRentals = $this->reportService->OverdueReport();

        $summary = [
            'total_overdue' => $overdueRentals->count(),
            'by_category' => [
                'recently_overdue' => $overdueRentals->where('overdue_category', 'recently_overdue')->count(),
                'moderately_overdue' => $overdueRentals->where('overdue_category', 'moderately_overdue')->count(),
                'severely_overdue' => $overdueRentals->where('overdue_category', 'severely_overdue')->count(),
            ]
        ];

        return response()->json([
            'summary' => $summary,
            'overdue_rentals' => $overdueRentals
        ]);
    }

    public function getActiveRentals(Rental $activeRentals)
    {
        $activeRentals = $this->reportService->ActiveRentals($activeRentals);
        return $this->ActiveRentalsResponses($activeRentals, 'Success', 200);
    }

    public function getBooksAvailability()
    {
        $books = $this->reportService->BooksAvailability();
        $summary = [
            'total_books' => $books->count(),
            'fully_available' => $books->where('availability_status', 'fully_available')->count(),
            'partially_available' => $books->where('availability_status', 'partially_available')->count(),
            'out_of_stock' => $books->where('availability_status', 'out_of_stock')->count(),
            'average_utilization' => $books->avg('utilization_rate')
        ];

        return response()->json([
            'summary' => $summary,
            'books' => $books
        ]);
    }

    public function getMonthlySummary(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $rentalsThisMonth = Rental::whereBetween('rental_date', [$startDate, $endDate]);
        $returnsThisMonth = Rental::whereBetween('return_date', [$startDate, $endDate]);

        $summary = [
            'period' => $startDate->format('F Y'),
            'new_rentals' => $rentalsThisMonth->count(),
            'returned_books' => $returnsThisMonth->whereNotNull('return_date')->count(),
            'still_active' => $rentalsThisMonth->where('status', 'active')->count(),
            'overdue_in_period' => $rentalsThisMonth->where('due_date', '<', Carbon::now())->where('status', 'active')->count(),
        ];

        $dailyStats = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dayRentals = Rental::whereDate('rental_date', $currentDate)->count();
            $dayReturns = Rental::whereDate('return_date', $currentDate)->count();

            $dailyStats[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day_name' => $currentDate->format('l'),
                'rentals' => $dayRentals,
                'returns' => $dayReturns,
                'net_activity' => $dayRentals - $dayReturns
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'summary' => $summary,
            'daily_breakdown' => $dailyStats
        ]);
    }

    private function formatPeriod($period, $type)
    {
        return match ($type) {
            'day' => Carbon::parse($period)->format('M d, Y'),
            'week' => "Week $period",
            'month' => Carbon::parse($period . '-01')->format('F Y'),
            'year' => $period,
            default => $period
        };
    }
}
