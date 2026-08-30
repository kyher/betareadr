<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait BookValidationRules
{
    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    protected function bookRules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array{title: string, description: string|null} */
    public function bookAttributes(): array
    {
        $description = $this->validated('description');

        return [
            'title' => $this->string('title')->trim()->toString(),
            'description' => is_string($description) && trim($description) !== '' ? trim($description) : null,
        ];
    }
}
