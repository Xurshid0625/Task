<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('renter_name');
            $table->string('renter_email');
            $table->string('renter_phone');
            $table->date('rental_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
