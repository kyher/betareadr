<?php

namespace App\Http\Controllers;

use App\Models\BetaReadingListing;
use App\Models\Book;

abstract class Controller
{
    protected function authorizeListing(Book $book, BetaReadingListing $listing): void
    {
        abort_unless($book->authorProfile->user_id === request()->user()->id, 403);
        abort_unless($listing->book_id === $book->id, 404);
    }
}
