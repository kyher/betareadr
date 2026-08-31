<?php

namespace App\Http\Requests;

use App\Concerns\BetaReadingListingValidationRules;
use App\Models\BetaReadingListing;
use App\Models\Book;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBetaReadingListingRequest extends FormRequest
{
    use BetaReadingListingValidationRules;

    public function authorize(): bool
    {
        $book = $this->route('book');
        $listing = $this->route('listing');

        return $book instanceof Book
            && $listing instanceof BetaReadingListing
            && $listing->book_id === $book->id
            && $this->user() !== null
            && $book->authorProfile->user_id === $this->user()->id;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return $this->betaReadingListingRules();
    }
}
