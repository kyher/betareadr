<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait BetaReadingListingValidationRules
{
    /** @return array<string, array<int, ValidationRule|array<mixed>|string>> */
    protected function betaReadingListingRules(): array
    {
        return [
            'reading_brief' => ['required', 'string', 'min:1', 'max:2000'],
            'word_count' => ['required', 'integer', 'min:1', 'max:2000000'],
        ];
    }

    /** @return array{reading_brief: string, word_count: int} */
    public function betaReadingListingAttributes(): array
    {
        return [
            'reading_brief' => $this->string('reading_brief')->trim()->toString(),
            'word_count' => $this->integer('word_count'),
        ];
    }
}
