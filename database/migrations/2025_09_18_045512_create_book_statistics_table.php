<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->integer('total_rentals')->default(0);
            $table->integer('total_returns')->default(0);
            $table->integer('current_active_rentals')->default(0);
            $table->integer('overdue_count')->default(0);
            $table->decimal('average_rental_duration', 5, 2)->nullable();
            $table->decimal('popularity_score', 8, 2)->default(0);
            $table->date('last_rented_date')->nullable();
            $table->date('last_returned_date')->nullable();
            $table->timestamps();

            $table->index(['book_id']);
            $table->index(['total_rentals']);
            $table->index(['popularity_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_statistics');
    }
};
