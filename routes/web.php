<?php

use App\Http\Controllers\AdminAuthenticationController;
use App\Http\Controllers\AdminReviewController;
use App\Http\Controllers\AuthorProfileController;
use App\Http\Controllers\BetaReaderProfileController;
use App\Http\Controllers\BetaReadingListingController;
use App\Http\Controllers\BetaReadingListingReviewController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\MatchController;
use App\Models\Administrator;
use App\Models\AuthorProfile;
use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthenticationController::class, 'create'])->name('login');
    Route::post('login', [AdminAuthenticationController::class, 'store'])->name('login.store');

    Route::middleware('auth:'.Administrator::AUTH_GUARD)->group(function () {
        Route::get('/', fn () => Inertia::render('admin/Dashboard', [
            'counts' => [
                'users' => User::count(),
                'authorProfiles' => AuthorProfile::count(),
                'betaReaderProfiles' => BetaReaderProfile::count(),
                'books' => Book::count(),
                'openListings' => BetaReadingListing::whereNull('closed_at')->count(),
                'listings' => BetaReadingListing::count(),
            ],
        ]))->name('dashboard');
        Route::get('users', [AdminReviewController::class, 'users'])->name('users.index');
        Route::delete('users/{user}', [AdminReviewController::class, 'destroyUser'])->name('users.destroy');
        Route::delete('books/{book}', [AdminReviewController::class, 'destroyBook'])->name('books.destroy');
        Route::delete('listings/{listing}', [AdminReviewController::class, 'destroyListing'])->name('listings.destroy');
        Route::get('author-profiles', [AdminReviewController::class, 'authorProfiles'])->name('author-profiles.index');
        Route::get('beta-reader-profiles', [AdminReviewController::class, 'betaReaderProfiles'])->name('beta-reader-profiles.index');
        Route::get('books', [AdminReviewController::class, 'books'])->name('books.index');
        Route::get('listings', [AdminReviewController::class, 'listings'])->name('listings.index');
        Route::post('logout', [AdminAuthenticationController::class, 'destroy'])->name('logout');
    });
});

Route::post('language', function (Request $request) {
    $locale = $request->validate([
        'locale' => ['required', Rule::in(array_map('strval', array_keys(config('app.supported_locales'))))],
    ])['locale'];

    $request->user()?->update(['locale' => $locale]);

    return back()->withCookie(cookie()->forever('locale', $locale));
})->name('language.update');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        $user = request()->user(User::AUTH_GUARD);

        return Inertia::render('Dashboard', [
            'authorProfile' => $user->authorProfile === null ? null : $user->authorProfile->only(['display_name', 'handle', 'bio']) + ['avatar_url' => $user->authorProfile->avatarUrl()],
            'betaReaderProfile' => $user->betaReaderProfile === null ? null : $user->betaReaderProfile->only(['display_name', 'handle', 'bio']) + ['avatar_url' => $user->betaReaderProfile->avatarUrl()],
        ]);
    })->name('dashboard');

    Route::get('author-profile/create', [AuthorProfileController::class, 'create'])->name('author-profile.create');
    Route::post('author-profile', [AuthorProfileController::class, 'store'])->name('author-profile.store');
    Route::get('author-profile/edit', [AuthorProfileController::class, 'edit'])->name('author-profile.edit');
    Route::patch('author-profile', [AuthorProfileController::class, 'update'])->name('author-profile.update');

    Route::get('beta-reader-profile/create', [BetaReaderProfileController::class, 'create'])->name('beta-reader-profile.create');
    Route::post('beta-reader-profile', [BetaReaderProfileController::class, 'store'])->name('beta-reader-profile.store');
    Route::get('beta-reader-profile/edit', [BetaReaderProfileController::class, 'edit'])->name('beta-reader-profile.edit');
    Route::patch('beta-reader-profile', [BetaReaderProfileController::class, 'update'])->name('beta-reader-profile.update');

    Route::get('feed', [FeedController::class, 'index'])->name('feed.index');
    Route::post('feed/{listing}/interest', [FeedController::class, 'interest'])->name('feed.interest');
    Route::post('feed/{listing}/pass', [FeedController::class, 'pass'])->name('feed.pass');

    Route::get('matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('matches/{match}/conversation', [ConversationController::class, 'show'])->name('matches.conversation');
    Route::get('matches/{match}/messages', [ConversationController::class, 'index'])->name('matches.messages.index');
    Route::post('matches/{match}/messages', [ConversationController::class, 'store'])->name('matches.messages.store');
    Route::post('matches/{match}/read', [ConversationController::class, 'read'])->name('matches.read');
    Route::delete('matches/{match}', [MatchController::class, 'end'])->name('matches.end');

    Route::resource('books', BookController::class)->except('show');

    Route::prefix('books/{book}/listings')->name('books.listings.')->group(function () {
        Route::get('/', [BetaReadingListingController::class, 'index'])->name('index');
        Route::get('create', [BetaReadingListingController::class, 'create'])->name('create');
        Route::post('/', [BetaReadingListingController::class, 'store'])->name('store');
        Route::get('{listing}/edit', [BetaReadingListingController::class, 'edit'])->name('edit');
        Route::patch('{listing}', [BetaReadingListingController::class, 'update'])->name('update');
        Route::patch('{listing}/close', [BetaReadingListingController::class, 'close'])->name('close');
        Route::patch('{listing}/reopen', [BetaReadingListingController::class, 'reopen'])->name('reopen');
        Route::get('{listing}/readers', [BetaReadingListingReviewController::class, 'index'])->name('readers.index');
        Route::post('{listing}/interests/{interest}/approve', [BetaReadingListingReviewController::class, 'approve'])->name('readers.approve');
        Route::post('{listing}/interests/{interest}/decline', [BetaReadingListingReviewController::class, 'decline'])->name('readers.decline');
    });
});

Route::get('authors/{authorProfile}', [AuthorProfileController::class, 'show'])->name('authors.show');
Route::get('beta-readers/{betaReaderProfile}', [BetaReaderProfileController::class, 'show'])->name('beta-readers.show');

require __DIR__.'/settings.php';
