<?php

namespace App\Http\Requests;

use App\Concerns\PublicProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAuthorProfileRequest extends FormRequest
{
    use PublicProfileValidationRules;

    /** @return array{display_name: string, bio: string|null} */
    public function profileAttributes(): array
    {
        $bio = $this->validated('bio');

        return [
            'display_name' => $this->string('display_name')->toString(),
            'bio' => is_string($bio) ? $bio : null,
        ];
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return $this->publicProfileUpdateRules();
    }
}
