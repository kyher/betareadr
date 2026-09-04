<?php

namespace App\Concerns;

use App\Enums\ContentLanguage;
use App\Models\BetaReaderProfile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait BetaReaderProfileValidationRules
{
    /** @return array<string, mixed> */
    protected function readingLanguageRules(): array
    {
        return [
            'reading_languages' => ['required', 'array', 'min:1'],
            'reading_languages.*' => [
                'distinct',
                Rule::in([BetaReaderProfile::ANY_LANGUAGE, ...array_column(ContentLanguage::cases(), 'value')]),
            ],
        ];
    }

    /** @return list<string> */
    protected function readingLanguages(): array
    {
        $languages = $this->validated('reading_languages');

        if (in_array(BetaReaderProfile::ANY_LANGUAGE, $languages, true) && count($languages) !== 1) {
            throw ValidationException::withMessages([
                'reading_languages' => __('Any language cannot be combined with individual languages.'),
            ]);
        }

        return array_values($languages);
    }
}
