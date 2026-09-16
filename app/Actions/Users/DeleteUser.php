<?php

namespace App\Actions\Users;

use App\Actions\BetaReadingListings\EndBetaReadingMatch;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    public function handle(User $user, EndBetaReadingMatch $endMatch): void
    {
        DB::transaction(function () use ($user, $endMatch): void {
            User::query()->lockForUpdate()->findOrFail($user->id);

            $matches = BetaReadingMatch::query()
                ->whereHas('readerDecision', fn ($query) => $query
                    ->whereHas('betaReaderProfile', fn ($query) => $query->where('user_id', $user->id))
                    ->orWhereHas('betaReadingListing.book.authorProfile', fn ($query) => $query->where('user_id', $user->id)))
                ->lockForUpdate()->get();

            foreach ($matches as $match) {
                $decision = $match->readerDecision;
                $book = $decision->betaReadingListing->book;
                $isAuthor = $book->authorProfile->user_id === $user->id;
                $endMatch->handle($match, $user);
                $match->forceFill([
                    'remaining_user_id' => $isAuthor ? $decision->betaReaderProfile->user_id : $book->authorProfile->user_id,
                    'remaining_role' => $isAuthor ? 'beta_reader' : 'author',
                    'retained_book_title' => $book->title,
                    'beta_reading_listing_decision_id' => null,
                ])->save();
            }

            $user->delete();
        });
    }
}
