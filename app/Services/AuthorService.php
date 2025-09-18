<?php

namespace App\Services;

use App\Models\Author;

class AuthorService
{
    public function indexAuthor($author)
    {
        return $author = Author::withCount('books')->paginate(2);
    }

    public function createAuthor($author)
    {
        return Author::create($author);
    }

    public function updateAuthor(Author $author, $validated)
    {
        $author->update($validated);
        return $author;
    }

    public function showAuthor($author)
    {
        return $author->load('books');
    }

    public function deleteAuthor($author)
    {
        return $author->delete();
    }
}
