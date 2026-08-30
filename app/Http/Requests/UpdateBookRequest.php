<?php

namespace App\Http\Requests;

use App\Concerns\BookValidationRules;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    use BookValidationRules;

    public function authorize(): bool
    {
        $book = $this->route('book');

        return $book instanceof Book
            && $this->user() !== null
            && $book->authorProfile->user_id === $this->user()->id;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return $this->bookRules();
    }
}
