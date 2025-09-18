<?php

namespace App\Models;

use App\Models\Report\BookStatistic;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'description',
        'isbn',
        'published_date',
        'pages',
        'language',
        'total_copies',
        'available_copies',
    ];


    protected $casts = [
        'published_date' => 'date',
    ];

    public function statistic()
    {
        return $this->hasOne(BookStatistic::class);
    }

    protected static function booted()
    {
        static::created(function ($book) {
            BookStatistic::create(['book_id' => $book->id]);
        });

        static::deleting(function ($book) {
            $book->statistic()->delete();
        });
    }

    public function updateStatistics()
    {
        if (!$this->statistic) {
            BookStatistic::create(['book_id' => $this->id]);
        }

        $this->statistic->updateStatistics();
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRentals()
    {
        return $this->hasMany(Rental::class)->where('status', 'active');
    }

    public function isAvailable()
    {
        return $this->available_copies > 0;
    }

    public function getRentalCountAttribute()
    {
        return $this->rentals()->count();
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('title', 'like', "%{$search}%")
            ->orWhereHas('author', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
    }
}
