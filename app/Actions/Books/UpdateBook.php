<?php

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateBook
{
    /** @param array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} $attributes */
    public function handle(Book $book, array $attributes, ?UploadedFile $coverImage = null, bool $removeCoverImage = false): void
    {
        $oldPath = $book->cover_image_path;
        $newPath = $coverImage?->store('book-covers', 'r2');

        if ($newPath === false) {
            throw new RuntimeException('Could not store book cover image.');
        }

        $coverImagePath = $newPath ?? ($removeCoverImage ? null : $oldPath);

        try {
            $book->update([...$attributes, 'cover_image_path' => $coverImagePath]);
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('r2')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath !== null && $oldPath !== $coverImagePath) {
            Storage::disk('r2')->delete($oldPath);
        }
    }
}
