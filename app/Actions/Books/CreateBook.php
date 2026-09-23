<?php

namespace App\Actions\Books;

use App\Models\AuthorProfile;
use App\Models\Book;

class CreateBook
{
    /** @param array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} $attributes */
    public function handle(AuthorProfile $authorProfile, array $attributes): Book
    {
        return $authorProfile->books()->create($attributes);
    }
}
