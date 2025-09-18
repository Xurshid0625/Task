<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends Controller
{

    public function __construct(protected BookService $bookService) {}

    public function index(Request $request)
    {
        $query = Book::with('author');

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $book = $query->paginate(3);

        return $this->bookResponses($book, 200);
    }

    public function store(StoreBookRequest $request)
    {
        $book = $this->bookService->createBook($request->validated());
        return $this->bookResponses($book, 'Book created successfully!', 200);
    }

    public function show(Book $book)
    {
        $book = $this->bookService->showBook($book);
        return $this->bookResponses($book, 'Book retrivied successfully!', 200);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $book = $this->bookService->updateBook($book, $request->validated());
        return $this->bookResponses($book, 'Book Updated successfully!', 200);
    }

    public function destroy(Book $book)
    {
        $book = $this->bookService->deleteBook($book);
        return $this->bookResponses('Book deleted successfully!', 200);
    }
}
