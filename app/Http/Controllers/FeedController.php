<?php

namespace App\Http\Controllers;

use App\Actions\Feed\FindNextFeedListing;
use App\Actions\Feed\RecordFeedDecision;
use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request, FindNextFeedListing $findNextListing): Response|RedirectResponse
    {
        $profile = $this->profileOrRedirect($request);

        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $listing = $findNextListing->handle($profile);

        return Inertia::render('Feed', [
            'listing' => $listing === null ? null : [
                'id' => $listing->id,
                'reading_brief' => $listing->reading_brief,
                'word_count' => $listing->word_count,
                'book' => [
                    'title' => $listing->book->title,
                    'description' => $listing->book->description,
                ],
                'author' => [
                    'display_name' => $listing->book->authorProfile->display_name,
                    'handle' => $listing->book->authorProfile->handle,
                ],
            ],
        ]);
    }

    public function interest(
        Request $request,
        BetaReadingListing $listing,
        RecordFeedDecision $recordDecision,
    ): RedirectResponse {
        return $this->decide($request, $listing, BetaReadingListingDecision::INTERESTED, $recordDecision);
    }

    public function pass(
        Request $request,
        BetaReadingListing $listing,
        RecordFeedDecision $recordDecision,
    ): RedirectResponse {
        return $this->decide($request, $listing, BetaReadingListingDecision::PASSED, $recordDecision);
    }

    private function decide(
        Request $request,
        BetaReadingListing $listing,
        string $decision,
        RecordFeedDecision $recordDecision,
    ): RedirectResponse {
        $profile = $this->profileOrRedirect($request);

        if ($profile instanceof RedirectResponse) {
            return $profile;
        }

        $result = $recordDecision->handle($profile, $listing, $decision);

        if ($result === RecordFeedDecision::CLOSED) {
            return to_route('feed.index')
                ->withErrors(['listing' => __('This Listing is no longer open.')]);
        }

        if ($result === RecordFeedDecision::CREATED && $decision === BetaReadingListingDecision::INTERESTED) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('Interest sent.')]);
        }

        return to_route('feed.index');
    }

    private function profileOrRedirect(Request $request): BetaReaderProfile|RedirectResponse
    {
        $profile = $request->user()->betaReaderProfile;

        if ($profile === null) {
            return to_route('beta-reader-profile.create')
                ->withErrors(['profile' => __('Create a beta reader profile to use the Feed.')]);
        }

        return $profile;
    }
}
