<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Report\RentalStatistic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StatisticsSeeder extends Seeder
{
    public function run(): void
    {
        Book::whereDoesntHave('statistic')->each(function ($book) {
            $book->updateStatistics();
        });

        Author::whereDoesntHave('statistic')->each(function ($author) {
            $author->updateStatistics();
        });

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            RentalStatistic::generateDailyStats($date);
        }
    }
}
