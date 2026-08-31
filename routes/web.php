<?php

use App\Http\Controllers\AuthorProfileController;
use App\Http\Controllers\BetaReaderProfileController;
use App\Http\Controllers\BetaReadingListingController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

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

    Route::resource('books', BookController::class)->except('show');

    Route::prefix('books/{book}/listings')->name('books.listings.')->group(function () {
        Route::get('/', [BetaReadingListingController::class, 'index'])->name('index');
        Route::get('create', [BetaReadingListingController::class, 'create'])->name('create');
        Route::post('/', [BetaReadingListingController::class, 'store'])->name('store');
        Route::get('{listing}/edit', [BetaReadingListingController::class, 'edit'])->name('edit');
        Route::patch('{listing}', [BetaReadingListingController::class, 'update'])->name('update');
        Route::patch('{listing}/close', [BetaReadingListingController::class, 'close'])->name('close');
        Route::patch('{listing}/reopen', [BetaReadingListingController::class, 'reopen'])->name('reopen');
    });
});

Route::get('authors/{authorProfile}', [AuthorProfileController::class, 'show'])->name('authors.show');
Route::get('beta-readers/{betaReaderProfile}', [BetaReaderProfileController::class, 'show'])->name('beta-readers.show');

require __DIR__.'/settings.php';
