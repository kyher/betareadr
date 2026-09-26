<?php

namespace App\Http\Requests;

use App\Concerns\BetaReaderProfileValidationRules;
use App\Concerns\PublicProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBetaReaderProfileRequest extends FormRequest
{
    use BetaReaderProfileValidationRules;
    use PublicProfileValidationRules;

    /** @return array{display_name: string, bio: string|null, reading_languages: list<string>, preferred_genres: list<string>} */
    public function profileAttributes(): array
    {
        $bio = $this->validated('bio');

        return [
            'display_name' => $this->string('display_name')->toString(),
            'bio' => is_string($bio) ? $bio : null,
            'reading_languages' => $this->readingLanguages(),
            'preferred_genres' => $this->preferredGenres(),
            'content_boundaries' => $this->contentBoundaries(),
        ];
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [...$this->publicProfileUpdateRules(), ...$this->readingLanguageRules(), ...$this->preferredGenreRules(), ...$this->contentBoundaryRules()];
    }
}
