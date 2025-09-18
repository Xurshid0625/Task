<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\ReportController;

Route::get('authors', [AuthorController::class, 'index']);
Route::post('authors', [AuthorController::class, 'store']);
Route::get('authors/{author}', [AuthorController::class, 'show']);
Route::put('authors/{author}', [AuthorController::class, 'update']);
Route::delete('authors/{author}', [AuthorController::class, 'destroy']);

Route::get('books', [BookController::class, 'index']);
Route::post('books', [BookController::class, 'store']);
Route::get('books/{book}', [BookController::class, 'show']);
Route::put('books/{book}', [BookController::class, 'update']);
Route::delete('books/{book}', [BookController::class, 'destroy']);

Route::get('rentals', [RentalController::class, 'index']);
Route::get('rentals/overdue', [RentalController::class, 'overdue']);
Route::get('rentals/active', [RentalController::class, 'active']);
Route::post('rentals', [RentalController::class, 'store']);
Route::get('rentals/{rental}', [RentalController::class, 'show']);
Route::put('rentals/{rental}/return', [RentalController::class, 'rentReturn']);

Route::prefix('reports')->group(function () {
    Route::get('report', [ReportController::class, 'index']);
    Route::get('/statistics', [ReportController::class, 'getCurrentStatistics']);
    Route::get('/most-rented-books', [ReportController::class, 'getMostRentedBooks']);
    Route::get('/author-statistics', [ReportController::class, 'getAuthorStatistics']);
    Route::get('/rental-trends', [ReportController::class, 'getRentalTrends']);
    Route::get('/overdue-report', [ReportController::class, 'getOverdueReport']);
    Route::get('/active-rentals', [ReportController::class, 'getActiveRentals']);
    Route::get('/books-availability', [ReportController::class, 'getBooksAvailability']);
    Route::get('/monthly-summary', [ReportController::class, 'getMonthlySummary']);
});
