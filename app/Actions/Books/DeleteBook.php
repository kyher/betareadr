<?php

namespace App\Actions\Books;

use App\Models\Book;

class DeleteBook
{
    public function handle(Book $book): bool
    {
        if ($book->hasActiveMatch()) {
            return false;
        }

        $book->delete();

        return true;
    }
}
