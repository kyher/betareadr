<?php

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

class DeleteBook
{
    public function handle(Book $book): bool
    {
        if ($book->hasActiveMatch()) {
            return false;
        }

        $coverImagePath = $book->cover_image_path;
        $book->delete();

        if ($coverImagePath !== null) {
            Storage::disk('r2')->delete($coverImagePath);
        }

        return true;
    }
}
