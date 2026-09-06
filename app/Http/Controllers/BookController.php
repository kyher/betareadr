<?php

namespace App\Http\Controllers;

use App\Actions\Books\CreateBook;
use App\Actions\Books\UpdateBook;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\AuthorProfile;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $profile = $this->authorProfile();

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        return Inertia::render('books/Index', [
            'books' => $profile->books()
                ->latest()
                ->latest('id')
                ->paginate(12)
                ->through(fn (Book $book): array => $book->only(['id', 'title', 'description', 'language'])),
        ]);
    }

    public function create(): Response|RedirectResponse
    {
        if ($this->authorProfile() === null) {
            return to_route('author-profile.create');
        }

        return Inertia::render('books/Create');
    }

    public function store(StoreBookRequest $request, CreateBook $createBook): RedirectResponse
    {
        $profile = $this->user()->authorProfile;

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        $createBook->handle($profile, $request->bookAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book added.')]);

        return to_route('books.index');
    }

    public function edit(Book $book): Response
    {
        $this->authorizeOwner($book);

        return Inertia::render('books/Edit', [
            'book' => $book->only(['id', 'title', 'description', 'language']),
        ]);
    }

    public function update(UpdateBookRequest $request, Book $book, UpdateBook $updateBook): RedirectResponse
    {
        $this->authorizeOwner($book);
        $updateBook->handle($book, $request->bookAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book updated.')]);

        return to_route('books.index');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorizeOwner($book);

        if ($book->betaReadingListings()
            ->whereHas('readerDecisions.match', fn ($match) => $match->whereNull('ended_at'))
            ->exists()) {
            return back()->withErrors(['book' => __('End active matches before deleting this book.')]);
        }

        $book->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Book deleted.')]);

        return to_route('books.index');
    }

    private function authorProfile(): ?AuthorProfile
    {
        return $this->user()->authorProfile;
    }

    private function authorizeOwner(Book $book): void
    {
        abort_unless($book->authorProfile->user_id === $this->user()->id, 403);
    }
}
