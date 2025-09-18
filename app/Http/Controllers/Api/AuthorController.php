<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Models\Author;
use App\Services\AuthorService;

class AuthorController extends Controller
{
    public function __construct(protected AuthorService $authorService) {}

    public function index(Author $author)
    {
        $author = $this->authorService->indexAuthor($author);
        return $this->authorResponse($author, 200);
    }

    public function store(StoreAuthorRequest $request)
    {
        $author = $this->authorService->createAuthor($request->validated());
        return $this->authorResponse($author, 'Author created successfully', 201);
    }

    public function show(Author $author)
    {
        $author = $this->authorService->showAuthor($author);
        return $this->authorResponse($author, 'Author retrivied successfully!', 201);
    }

    public function update(UpdateAuthorRequest $request, Author $author)
    {
        $author = $this->authorService->updateAuthor($author, $request->validated());
        return $this->authorResponse($author, 'Author Updated successfully!', 200);
    }

    public function destroy(Author $author)
    {
        $author = $this->authorService->deleteAuthor($author);
        return $this->authorResponse('Author deleted successfully!', 200);
    }
}
