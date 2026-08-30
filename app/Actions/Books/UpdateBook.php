<?php

namespace App\Actions\Books;

use App\Models\Book;

class UpdateBook
{
    /** @param array{title: string, description: string|null} $attributes */
    public function handle(Book $book, array $attributes): void
    {
        $book->update($attributes);
    }
}
