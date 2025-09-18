<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('year');
            $table->integer('month');
            $table->integer('day');
            $table->integer('new_rentals')->default(0);
            $table->integer('returned_books')->default(0);
            $table->integer('overdue_books')->default(0);
            $table->integer('active_rentals_end_of_day')->default(0);
            $table->decimal('utilization_rate', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['date']);
            $table->index(['year', 'month']);
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_statistics');
    }
};
