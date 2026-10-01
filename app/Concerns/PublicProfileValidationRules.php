<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait PublicProfileValidationRules
{
    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    protected function publicProfileRules(string $profileTable): array
    {
        return [
            ...$this->publicProfileUpdateRules(),
            'handle' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
                Rule::notIn(['admin', 'dashboard', 'login', 'register', 'settings']),
                Rule::unique($profileTable, 'handle'),
            ],
        ];
    }

    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    protected function publicProfileUpdateRules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'handle.regex' => 'The handle may contain lowercase letters, numbers, and single hyphens only.',
            'handle.not_in' => 'This handle is reserved.',
        ];
    }
}
