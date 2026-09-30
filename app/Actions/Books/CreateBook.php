<?php

namespace App\Actions\Books;

use App\Models\AuthorProfile;
use App\Models\Book;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CreateBook
{
    /** @param array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} $attributes */
    public function handle(AuthorProfile $authorProfile, array $attributes, ?UploadedFile $coverImage = null): Book
    {
        $coverImagePath = $coverImage?->store('book-covers', 'r2');

        if ($coverImagePath === false) {
            throw new RuntimeException('Could not store book cover image.');
        }

        try {
            return $authorProfile->books()->create([...$attributes, 'cover_image_path' => $coverImagePath]);
        } catch (Throwable $exception) {
            if ($coverImagePath !== null) {
                Storage::disk('r2')->delete($coverImagePath);
            }

            throw $exception;
        }
    }
}
