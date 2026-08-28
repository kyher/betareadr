<?php

use App\Http\Controllers\AuthorProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard', [
            'authorProfile' => request()->user()->authorProfile?->only(['display_name', 'handle', 'bio']),
        ]);
    })->name('dashboard');

    Route::get('author-profile/create', [AuthorProfileController::class, 'create'])->name('author-profile.create');
    Route::post('author-profile', [AuthorProfileController::class, 'store'])->name('author-profile.store');
    Route::get('author-profile/edit', [AuthorProfileController::class, 'edit'])->name('author-profile.edit');
    Route::patch('author-profile', [AuthorProfileController::class, 'update'])->name('author-profile.update');
});

Route::get('authors/{authorProfile}', [AuthorProfileController::class, 'show'])->name('authors.show');

require __DIR__.'/settings.php';
