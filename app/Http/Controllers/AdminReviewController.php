<?php

namespace App\Http\Controllers;

use App\Actions\Users\DeleteUser;
use App\Models\AuthorProfile;
use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminReviewController extends Controller
{
    public function users(Request $request): Response
    {
        $search = $this->search($request);

        return Inertia::render('admin/Index', [
            'type' => 'users',
            'search' => $search,
            'records' => User::query()->with(['authorProfile', 'betaReaderProfile'])
                ->when($search, fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhereLike('email', "%{$search}%")))
                ->latest()->latest('id')->paginate(12)->withQueryString()
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'author' => $user->authorProfile?->only(['display_name', 'handle']),
                    'reader' => $user->betaReaderProfile?->only(['display_name', 'handle']),
                    'created_at' => $user->created_at,
                ]),
        ]);
    }

    public function destroyUser(User $user, DeleteUser $deleteUser): RedirectResponse
    {
        $deleteUser->handle($user);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User removed.')]);

        return back();
    }

    public function authorProfiles(Request $request): Response
    {
        $search = $this->search($request);

        return Inertia::render('admin/Index', [
            'type' => 'author-profiles',
            'search' => $search,
            'records' => AuthorProfile::query()->with('user:id,name')
                ->when($search, fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('display_name', "%{$search}%")
                    ->orWhereLike('handle', "%{$search}%")))
                ->latest()->latest('id')->paginate(12)->withQueryString()
                ->through(fn (AuthorProfile $profile) => [
                    'id' => $profile->id,
                    'name' => $profile->display_name,
                    'handle' => $profile->handle,
                    'user' => $profile->user->name,
                    'created_at' => $profile->created_at,
                ]),
        ]);
    }

    public function betaReaderProfiles(Request $request): Response
    {
        $search = $this->search($request);

        return Inertia::render('admin/Index', [
            'type' => 'beta-reader-profiles',
            'search' => $search,
            'records' => BetaReaderProfile::query()->with('user:id,name')
                ->when($search, fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('display_name', "%{$search}%")
                    ->orWhereLike('handle', "%{$search}%")))
                ->latest()->latest('id')->paginate(12)->withQueryString()
                ->through(fn (BetaReaderProfile $profile) => [
                    'id' => $profile->id,
                    'name' => $profile->display_name,
                    'handle' => $profile->handle,
                    'user' => $profile->user->name,
                    'created_at' => $profile->created_at,
                ]),
        ]);
    }

    public function books(Request $request): Response
    {
        $search = $this->search($request);

        return Inertia::render('admin/Index', [
            'type' => 'books',
            'search' => $search,
            'records' => Book::query()->with('authorProfile:id,display_name,handle')
                ->when($search, fn ($query) => $query->whereLike('title', "%{$search}%"))
                ->latest()->latest('id')->paginate(12)->withQueryString()
                ->through(fn (Book $book) => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->authorProfile->only(['display_name', 'handle']),
                    'language' => $book->language->label(),
                    'genre' => __($book->genre->label()),
                    'warnings' => $book->content_warnings,
                    'description' => $book->description,
                    'created_at' => $book->created_at,
                ]),
        ]);
    }

    public function listings(Request $request): Response
    {
        $search = $this->search($request);
        $status = $request->validate(['status' => 'nullable|in:all,open,closed'])['status'] ?? 'all';

        return Inertia::render('admin/Index', [
            'type' => 'listings',
            'search' => $search,
            'status' => $status,
            'records' => BetaReadingListing::query()->with('book.authorProfile')
                ->when($search, fn ($query) => $query->where(fn ($query) => $query
                    ->whereHas('book', fn ($query) => $query->whereLike('title', "%{$search}%"))
                    ->orWhereHas('book.authorProfile', fn ($query) => $query->whereLike('handle', "%{$search}%"))
                    ->when(ctype_digit($search), fn ($query) => $query->orWhereKey($search))))
                ->when($status === 'open', fn ($query) => $query->whereNull('closed_at'))
                ->when($status === 'closed', fn ($query) => $query->whereNotNull('closed_at'))
                ->latest()->latest('id')->paginate(12)->withQueryString()
                ->through(fn (BetaReadingListing $listing) => [
                    'id' => $listing->id,
                    'title' => $listing->book->title,
                    'author' => $listing->book->authorProfile->only(['display_name', 'handle']),
                    'closed_at' => $listing->closed_at,
                    'word_count' => $listing->word_count,
                    'reading_brief' => $listing->reading_brief,
                    'created_at' => $listing->created_at,
                ]),
        ]);
    }

    private function search(Request $request): string
    {
        return trim($request->validate(['q' => 'nullable|string|max:200'])['q'] ?? '');
    }
}
