<?php

namespace App\Http\Requests;

use App\Concerns\BetaReaderProfileValidationRules;
use App\Concerns\PublicProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBetaReaderProfileRequest extends FormRequest
{
    use BetaReaderProfileValidationRules;
    use PublicProfileValidationRules;

    /** @return array{display_name: string, handle: string, bio: string|null, reading_languages: list<string>} */
    public function profileAttributes(): array
    {
        $bio = $this->validated('bio');

        return [
            'display_name' => $this->string('display_name')->toString(),
            'handle' => $this->string('handle')->toString(),
            'bio' => is_string($bio) ? $bio : null,
            'reading_languages' => $this->readingLanguages(),
            'content_boundaries' => $this->contentBoundaries(),
        ];
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [...$this->publicProfileRules('beta_reader_profiles'), ...$this->readingLanguageRules(), ...$this->contentBoundaryRules()];
    }
}
