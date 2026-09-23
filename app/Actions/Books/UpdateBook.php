<?php

namespace App\Actions\Books;

use App\Models\Book;

class UpdateBook
{
    /** @param array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} $attributes */
    public function handle(Book $book, array $attributes): void
    {
        $book->update($attributes);
    }
}
