<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAuthorProfileRequest extends FormRequest
{
    /** @return array{display_name: string, handle: string, bio: string|null} */
    public function profileAttributes(): array
    {
        $bio = $this->validated('bio');

        return [
            'display_name' => $this->string('display_name')->toString(),
            'handle' => $this->string('handle')->toString(),
            'bio' => is_string($bio) ? $bio : null,
        ];
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'display_name' => ['required', 'string', 'min:2', 'max:100'],
            'handle' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
                Rule::notIn(['admin', 'dashboard', 'login', 'register', 'settings']),
                Rule::unique('author_profiles', 'handle'),
            ],
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'handle.regex' => 'The handle may contain lowercase letters, numbers, and single hyphens only.',
            'handle.not_in' => 'This handle is reserved.',
        ];
    }
}
