<?php

namespace App\Actions\Books;

use App\Models\AuthorProfile;
use App\Models\Book;

class CreateBook
{
    /** @param array{title: string, description: string|null} $attributes */
    public function handle(AuthorProfile $authorProfile, array $attributes): Book
    {
        return $authorProfile->books()->create($attributes);
    }
}
