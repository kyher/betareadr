<?php

namespace App\Concerns;

use App\Enums\ContentLanguage;
use App\Enums\ContentTopic;
use App\Enums\Genre;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait BookValidationRules
{
    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    protected function bookRules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'language' => ['required', Rule::enum(ContentLanguage::class)],
            'genre' => ['required', Rule::enum(Genre::class)],
            'content_warnings' => ['sometimes', 'array'],
            'content_warnings.*' => ['distinct', Rule::enum(ContentTopic::class)],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array{title: string, description: string|null, language: string, genre: string, content_warnings: list<string>} */
    public function bookAttributes(): array
    {
        $description = $this->validated('description');

        return [
            'title' => $this->string('title')->trim()->toString(),
            'description' => is_string($description) && trim($description) !== '' ? trim($description) : null,
            'language' => $this->string('language')->toString(),
            'genre' => $this->string('genre')->toString(),
            'content_warnings' => array_values($this->validated('content_warnings', [])),
        ];
    }
}
