<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('isbn')->unique();
            $table->date('published_date');
            $table->integer('pages');
            $table->string('language')->default('uzbek');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
