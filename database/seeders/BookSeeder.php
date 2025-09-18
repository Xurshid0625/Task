<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Book::create([
            'title' => "O'tkan kunlar",
            'description' => "O'zbek adabiyotining birinchi romani",
            'isbn' => "978-9943-01-123-4",
            'published_date' => "1925-01-01",
            'pages' => 400,
            'language' => "uzbek",
            'total_copies' => 5,
            'available_copies' => 5,
            'author_id' => 1,
        ]);

        Book::create([
            'title' => "O'tkan kunlar2",
            'description' => "O'zbek adabiyotining birinchi romani",
            'isbn' => "978-9943-01-123-5",
            'published_date' => "1925-01-01",
            'pages' => 400,
            'language' => "uzbek",
            'total_copies' => 5,
            'available_copies' => 5,
            'author_id' => 1,
        ]);

        Book::create([
            'title' => "O'tkan kunlar3",
            'description' => "O'zbek adabiyotining birinchi romani",
            'isbn' => "978-9943-01-123-6",
            'published_date' => "1925-01-01",
            'pages' => 400,
            'language' => "uzbek",
            'total_copies' => 5,
            'available_copies' => 5,
            'author_id' => 1,
        ]);
    }
}
