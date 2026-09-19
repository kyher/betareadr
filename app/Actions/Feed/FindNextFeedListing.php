<?php

namespace App\Actions\Feed;

use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;

class FindNextFeedListing
{
    public function handle(BetaReaderProfile $profile): ?BetaReadingListing
    {
        return BetaReadingListing::query()
            ->whereNull('closed_at')
            ->whereDoesntHave('readerDecisions', fn ($query) => $query
                ->where('beta_reader_profile_id', $profile->id))
            ->whereHas('book.authorProfile', fn ($query) => $query
                ->where('user_id', '!=', $profile->user_id))
            ->when(! in_array(BetaReaderProfile::ANY_LANGUAGE, $profile->reading_languages, true), fn ($query) => $query
                ->whereHas('book', fn ($query) => $query->whereIn('language', $profile->reading_languages)))
            ->when($profile->content_boundaries, fn ($query) => $query
                ->whereDoesntHave('book', fn ($books) => $books->where(function ($warnings) use ($profile) {
                    foreach ($profile->content_boundaries as $boundary) {
                        $warnings->orWhereJsonContains('content_warnings', $boundary);
                    }
                })))
            ->with('book.authorProfile')
            ->oldest()
            ->oldest('id')
            ->first();
    }
}
