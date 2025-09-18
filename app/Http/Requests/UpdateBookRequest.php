<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'isbn' => 'required|string|unique:books,isbn,' . $this->route('book')->id,
            'published_date' => 'required|date',
            'pages' => 'required|integer|min:1',
            'language' => 'required|string',
            'total_copies' => 'required|integer|min:1',
            'author_id' => 'required|exists:authors,id',
        ];
    }
}
