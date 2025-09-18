<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentRequest;
use App\Models\Book;
use App\Models\Rental;
use App\Services\RentService;

class RentalController extends Controller
{
    public function __construct(protected RentService $rentService) {}

    public function index(Rental $rental)
    {
        $rental = $this->rentService->indexRent($rental);
        return $this->rentResponses($rental, 200);
    }

    public function store(StoreRentRequest $request)
    {
        $rental = $this->rentService->storeRent($request->validated());
        return $this->bookResponses($rental, 'Rent created successfully!', 200);
    }

    public function show(Book $book, Rental $rental)
    {
        $book = $this->rentService->showRent($book);
        return $this->bookResponses($rental, 'Rent retrivied successfully!', 200);
    }

    public function rentReturn(Rental $rental)
    {
        $rental = $this->rentService->rentReturn($rental);
        return $this->bookResponses($rental, 'Rent return successfully!', 200);
    }

    public function overdue(Rental $rental)
    {
        $rental = Rental::overdue()->with(['book.author'])->paginate(10);
        return $this->rentResponses($rental, 'Deferred rents', 200);
    }

    public function active(Rental $rental)
    {
        $rental = Rental::active()->with(['book.author'])->paginate(10);
        return $this->rentResponses($rental, 'Active rents', 200);
    }
}
