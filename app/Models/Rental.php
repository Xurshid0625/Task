<?php

namespace App\Models;

use App\Models\Report\RentalStatistic;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'book_id',
        'renter_name',
        'renter_email',
        'renter_phone',
        'rental_date',
        'due_date',
        'return_date',
        'status'
    ];

    protected $casts = [
        'rental_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($rental) {
            $rental->updateRelatedStatistics();
        });

        static::updated(function ($rental) {
            $rental->updateRelatedStatistics();
        });
    }
    public function updateRelatedStatistics()
    {
        $this->book->updateStatistics();

        $this->book->author->updateStatistics();

        RentalStatistic::generateDailyStats($this->rental_date);

        if ($this->return_date) {
            RentalStatistic::generateDailyStats($this->return_date);
        }
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function isOverdue()
    {
        return $this->status === 'active' && $this->due_date < Carbon::today();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->where('due_date', '<', Carbon::today());
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }
}
