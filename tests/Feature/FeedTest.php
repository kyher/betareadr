<?php

namespace Tests\Feature;

use App\Models\BetaReaderProfile;
use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_or_decide_feed_listings(): void
    {
        $listing = $this->listingFor($this->author());

        $this->get(route('feed.index'))->assertRedirect(route('login'));
        $this->post(route('feed.interest', $listing))->assertRedirect(route('login'));
        $this->post(route('feed.pass', $listing))->assertRedirect(route('login'));
    }

    public function test_user_without_beta_reader_profile_is_sent_to_profile_creation(): void
    {
        $user = User::factory()->create();
        $listing = $this->listingFor($this->author());

        $this->actingAs($user)->get(route('feed.index'))
            ->assertRedirect(route('beta-reader-profile.create'))
            ->assertSessionHasErrors('profile');
        $this->actingAs($user)->post(route('feed.interest', $listing))
            ->assertRedirect(route('beta-reader-profile.create'))
            ->assertSessionHasErrors('profile');
    }

    public function test_feed_shows_oldest_eligible_listing_with_book_and_author_details(): void
    {
        [$reader, $profile] = $this->reader();
        $author = $this->author();
        $newer = $this->listingFor($author, 'Newer brief', bookTitle: 'Newer Book');
        $oldest = $this->listingFor($author, 'Oldest brief', bookTitle: 'Oldest Book');
        $oldest->book->update(['description' => 'An atmospheric mystery.']);
        $newer->forceFill(['created_at' => now()->subDay()])->save();
        $oldest->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->actingAs($reader)->get(route('feed.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Feed')
                ->where('listing.id', $oldest->id)
                ->where('listing.reading_brief', 'Oldest brief')
                ->where('listing.word_count', 80000)
                ->where('listing.book.title', 'Oldest Book')
                ->where('listing.book.description', 'An atmospheric mystery.')
                ->where('listing.author.display_name', 'An Author')
                ->where('listing.author.handle', $author->authorProfile->handle)
                ->where('auth.has_beta_reader_profile', true));

        $this->assertSame($profile->id, $reader->betaReaderProfile->id);
    }

    public function test_feed_excludes_closed_own_and_previously_decided_listings(): void
    {
        [$reader, $profile] = $this->reader(withAuthorProfile: true);
        $own = $this->listingFor($reader, bookTitle: 'Own Book');
        $closed = $this->listingFor($this->author('closed-author'), closed: true, bookTitle: 'Closed Book');
        $passed = $this->listingFor($this->author('passed-author'), bookTitle: 'Passed Book');
        $eligible = $this->listingFor($this->author('eligible-author'), bookTitle: 'Eligible Book');
        $profile->decisions()->create([
            'beta_reading_listing_id' => $passed->id,
            'decision' => BetaReadingListingDecision::PASSED,
        ]);

        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('listing.id', $eligible->id));

        $this->assertNotSame($own->id, $eligible->id);
        $this->assertNotSame($closed->id, $eligible->id);
    }

    public function test_language_compatibility_controls_feed_and_interest(): void
    {
        [$reader, $profile] = $this->reader();
        $profile->update(['reading_languages' => ['fr']]);
        $english = $this->listingFor($this->author('english-author'), bookTitle: 'English Book', language: 'en');
        $french = $this->listingFor($this->author('french-author'), bookTitle: 'French Book', language: 'fr');

        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('listing.id', $french->id)
                ->where('listing.book.language', 'fr'));

        $this->actingAs($reader)->post(route('feed.interest', $english))
            ->assertSessionHasErrors('listing');
        $this->assertSame(0, $profile->decisions()->count());

        $profile->update(['reading_languages' => ['*']]);
        $reader->unsetRelation('betaReaderProfile');
        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page->where('listing.id', $english->id));
    }

    public function test_interest_is_recorded_and_next_listing_is_shown(): void
    {
        [$reader, $profile] = $this->reader();
        $first = $this->listingFor($this->author('first-author'));
        $next = $this->listingFor($this->author('next-author'));
        $first->forceFill(['created_at' => now()->subDay()])->save();

        $this->actingAs($reader)->post(route('feed.interest', $first))
            ->assertRedirect(route('feed.index'));

        $this->assertDatabaseHas('beta_reading_listing_decisions', [
            'beta_reader_profile_id' => $profile->id,
            'beta_reading_listing_id' => $first->id,
            'decision' => BetaReadingListingDecision::INTERESTED,
        ]);
        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page->where('listing.id', $next->id));
    }

    public function test_pass_is_private_feed_state_and_produces_the_empty_state(): void
    {
        [$reader, $profile] = $this->reader();
        $listing = $this->listingFor($this->author());

        $this->actingAs($reader)->post(route('feed.pass', $listing))
            ->assertRedirect(route('feed.index'));

        $this->assertDatabaseHas('beta_reading_listing_decisions', [
            'beta_reader_profile_id' => $profile->id,
            'beta_reading_listing_id' => $listing->id,
            'decision' => BetaReadingListingDecision::PASSED,
        ]);
        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page->where('listing', null));
    }

    public function test_first_decision_wins_and_repeated_submission_is_harmless(): void
    {
        [$reader, $profile] = $this->reader();
        $listing = $this->listingFor($this->author());

        $this->actingAs($reader)->post(route('feed.pass', $listing));
        $this->actingAs($reader)->post(route('feed.pass', $listing))->assertSessionHasNoErrors();
        $this->actingAs($reader)->post(route('feed.interest', $listing))->assertSessionHasNoErrors();

        $this->assertSame(1, $profile->decisions()->count());
        $this->assertSame(BetaReadingListingDecision::PASSED, $profile->decisions()->first()->decision);
    }

    public function test_closed_listing_rejects_a_stale_decision(): void
    {
        [$reader, $profile] = $this->reader();
        $listing = $this->listingFor($this->author(), closed: true);

        $this->actingAs($reader)->post(route('feed.interest', $listing))
            ->assertRedirect(route('feed.index'))
            ->assertSessionHasErrors('listing');

        $this->assertSame(0, $profile->decisions()->count());
    }

    public function test_undecided_reopened_listing_returns_but_decided_listing_does_not(): void
    {
        [$reader, $profile] = $this->reader();
        $listing = $this->listingFor($this->author(), closed: true);

        $listing->update(['closed_at' => null]);
        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page->where('listing.id', $listing->id));

        $profile->decisions()->create([
            'beta_reading_listing_id' => $listing->id,
            'decision' => BetaReadingListingDecision::INTERESTED,
        ]);
        $listing->update(['closed_at' => now()]);
        $listing->update(['closed_at' => null]);

        $this->actingAs($reader)->get(route('feed.index'))
            ->assertInertia(fn (Assert $page) => $page->where('listing', null));
    }

    /** @return array{User, BetaReaderProfile} */
    private function reader(bool $withAuthorProfile = false): array
    {
        $user = User::factory()->create();
        $profile = $user->betaReaderProfile()->create([
            'display_name' => 'Keen Reader',
            'handle' => 'keen-reader',
        ]);

        if ($withAuthorProfile) {
            $user->authorProfile()->create([
                'display_name' => 'Reader Author',
                'handle' => 'reader-author',
            ]);
        }

        return [$user->refresh(), $profile];
    }

    private function author(string $handle = 'an-author'): User
    {
        $user = User::factory()->create();
        $user->authorProfile()->create([
            'display_name' => 'An Author',
            'handle' => $handle,
        ]);

        return $user->refresh();
    }

    private function listingFor(
        User $author,
        string $brief = 'A draft ready for feedback.',
        bool $closed = false,
        string $bookTitle = 'A Book',
        string $language = 'en',
    ): BetaReadingListing {
        $book = $author->authorProfile->books()->create([
            'title' => $bookTitle,
            'description' => 'A Book description.',
            'language' => $language,
        ]);

        return $book->betaReadingListings()->create([
            'reading_brief' => $brief,
            'word_count' => 80000,
            'closed_at' => $closed ? now() : null,
        ]);
    }
}
