<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->integer('total_books')->default(0);
            $table->integer('total_book_copies')->default(0);
            $table->integer('total_rentals')->default(0);
            $table->integer('total_returns')->default(0);
            $table->integer('current_active_rentals')->default(0);
            $table->decimal('average_rentals_per_book', 8, 2)->default(0);
            $table->decimal('popularity_score', 10, 2)->default(0);
            $table->date('last_rental_date')->nullable();
            $table->timestamps();

            $table->index(['author_id']);
            $table->index(['total_rentals']);
            $table->index(['popularity_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_statistics');
    }
};
