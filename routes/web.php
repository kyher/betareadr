<?php

use App\Http\Controllers\AuthorProfileController;
use App\Http\Controllers\BetaReaderProfileController;
use App\Http\Controllers\BetaReadingListingController;
use App\Http\Controllers\BetaReadingListingReviewController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\FeedController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::post('language', function (Request $request) {
    $locale = $request->validate([
        'locale' => ['required', Rule::in(array_map('strval', array_keys(config('app.supported_locales'))))],
    ])['locale'];

    $request->user()?->update(['locale' => $locale]);

    return back()->withCookie(cookie()->forever('locale', $locale));
})->name('language.update');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard', [
            'authorProfile' => request()->user()->authorProfile?->only(['display_name', 'handle', 'bio']),
            'betaReaderProfile' => request()->user()->betaReaderProfile?->only(['display_name', 'handle', 'bio']),
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
        Route::delete('{listing}/matches/{interest}', [BetaReadingListingReviewController::class, 'end'])->name('readers.end');
    });
});

Route::get('authors/{authorProfile}', [AuthorProfileController::class, 'show'])->name('authors.show');
Route::get('beta-readers/{betaReaderProfile}', [BetaReaderProfileController::class, 'show'])->name('beta-readers.show');

require __DIR__.'/settings.php';
