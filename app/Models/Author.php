<?php

namespace App\Models;

use App\Models\Report\AuthorStatistic;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'name',
        'bio',
        'birth_date',
        'nationality'
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function statistic()
    {
        return $this->hasOne(AuthorStatistic::class);
    }

    protected static function booted()
    {
        static::created(function ($author) {
            AuthorStatistic::create(['author_id' => $author->id]);
        });

        static::deleting(function ($author) {
            $author->statistic()->delete();
        });
    }

    public function updateStatistics()
    {
        if (!$this->statistic) {
            AuthorStatistic::create(['author_id' => $this->id]);
        }

        $this->statistic->updateStatistics();
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function getBooksCountAttribute()
    {
        return $this->books()->count();
    }

    public function getTotalRentalAttribute()
    {
        return $this->books()->withCount('rentals')->get()->sum('rentals_count');
    }
}
