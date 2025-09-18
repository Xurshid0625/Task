<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'renter_name' => 'required|string|max:255',
            'renter_email' => 'required|email',
            'renter_phone' => 'required|string|max:20',
            'rental_date' => 'required|date',
            'due_date' => 'required|date|after:rental_date',
        ];
    }
}
