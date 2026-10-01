<?php

namespace App\Actions\Books;

use App\Models\Book;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UpdateBook
{
    /** @param array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} $attributes */
    public function handle(Book $book, array $attributes, ?UploadedFile $coverImage = null, bool $removeCoverImage = false): void
    {
        $newPath = $coverImage?->store('book-covers', 'r2');

        if ($newPath === false) {
            throw new RuntimeException('Could not store book cover image.');
        }

        try {
            $oldPath = DB::transaction(function () use ($book, $attributes, $newPath, $removeCoverImage): ?string {
                $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
                $book->setRawAttributes($lockedBook->getAttributes(), true);
                $oldPath = $book->cover_image_path;
                $coverImagePath = $newPath ?? ($removeCoverImage ? null : $oldPath);

                $book->update([...$attributes, 'cover_image_path' => $coverImagePath]);

                return $oldPath !== $coverImagePath ? $oldPath : null;
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('r2')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath !== null) {
            Storage::disk('r2')->delete($oldPath);
        }
    }
}
