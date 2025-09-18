<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->index(['status']);
            $table->index(['rental_date']);
            $table->index(['due_date']);
            $table->index(['return_date']);
            $table->index(['status', 'due_date']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->index(['available_copies']);
            $table->index(['total_copies']);
            // $table->dropIndex(['author_id']);
            $table->fullText(['title', 'description']);
        });

        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'bio')) {
                $table->fullText(['name', 'bio']);
            } else {
                $table->fullText('name');
            }
        });
    }


    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['rental_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['return_date']);
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['available_copies']);
            $table->dropIndex(['total_copies']);
            // $table->dropIndex(['author_id']);
            $table->dropFullText(['title', 'description']);
        });

        Schema::table('authors', function (Blueprint $table) {
            if (Schema::hasColumn('authors', 'bio')) {
                $table->dropFullText(['name', 'bio']);
            } else {
                $table->dropFullText(['name']);
            }
        });
    }
};
