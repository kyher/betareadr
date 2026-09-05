<?php

namespace Tests\Feature;

use App\Models\BetaReadingListing;
use App\Models\BetaReadingListingDecision;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BetaReadingListingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_reviews_interest_and_manages_matches(): void
    {
        $author = $this->author();
        $book = $this->bookFor($author);
        $listing = $this->listingFor($book, closed: true);
        $oldest = $this->interestFor($listing, 'oldest-reader', now()->subDay());
        $newest = $this->interestFor($listing, 'newest-reader', now());

        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('beta-reading-listings/Review')
                ->where('listing.closed_at', fn ($value) => $value !== null)
                ->where('interested.0.id', $oldest->id)
                ->where('interested.0.profile.handle', 'oldest-reader')
                ->where('interested.1.id', $newest->id)
                ->has('approved', 0));

        $this->actingAs($author)->post(route('books.listings.readers.approve', [$book, $listing, $oldest]));
        $this->actingAs($author)->post(route('books.listings.readers.approve', [$book, $listing, $oldest]));
        $this->actingAs($author)->post(route('books.listings.readers.decline', [$book, $listing, $newest]));

        $this->assertSame(BetaReadingListingDecision::APPROVED, $oldest->refresh()->author_decision);
        $this->assertSame(1, $oldest->match()->count());
        $this->assertSame(BetaReadingListingDecision::DECLINED, $newest->refresh()->author_decision);
        $match = $oldest->match;

        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('approved.0.match_id', $match->id)
                ->where('approved.0.created_at', $oldest->created_at?->toISOString())
                ->where('approved.0.profile.bio', 'Current profile bio.')
                ->where('approved.0.profile.reading_languages', ['*']));

        $this->actingAs($author)->delete(route('books.destroy', $book))
            ->assertSessionHasErrors('book');
        $this->assertDatabaseHas('books', ['id' => $book->id]);

        $this->actingAs($author)->delete(route('books.listings.readers.end', [$book, $listing, $match]));
        $this->assertNotNull($match->refresh()->ended_at);

        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('interested', 0)
                ->has('approved', 0));

        $this->actingAs($author)->delete(route('books.destroy', $book))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_only_owner_can_review_and_interest_must_belong_to_listing(): void
    {
        $owner = $this->author('owner');
        $other = $this->author('other');
        $book = $this->bookFor($owner);
        $listing = $this->listingFor($book);
        $interest = $this->interestFor($listing, 'reader');
        $otherBook = $this->bookFor($owner, 'Other Book');
        $otherListing = $this->listingFor($otherBook);

        $this->actingAs($other)->get(route('books.listings.readers.index', [$book, $listing]))->assertForbidden();
        $this->actingAs($other)->post(route('books.listings.readers.approve', [$book, $listing, $interest]))->assertForbidden();
        $this->actingAs($owner)->post(route('books.listings.readers.approve', [$otherBook, $otherListing, $interest]))->assertNotFound();
    }

    public function test_interest_and_match_order_uses_id_to_break_timestamp_ties(): void
    {
        $author = $this->author();
        $book = $this->bookFor($author);
        $listing = $this->listingFor($book);
        $createdAt = now();
        $first = $this->interestFor($listing, 'first-reader', $createdAt);
        $second = $this->interestFor($listing, 'second-reader', $createdAt);

        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('interested.0.id', $first->id)
                ->where('interested.1.id', $second->id));

        $this->actingAs($author)->post(route('books.listings.readers.approve', [$book, $listing, $first]));
        $this->actingAs($author)->post(route('books.listings.readers.approve', [$book, $listing, $second]));
        $first->match()->update(['created_at' => $createdAt]);
        $second->match()->update(['created_at' => $createdAt]);

        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('approved.0.id', $second->id)
                ->where('approved.1.id', $first->id));
    }

    private function author(string $handle = 'author'): User
    {
        $user = User::factory()->create();
        $user->authorProfile()->create(['display_name' => 'An Author', 'handle' => $handle]);

        return $user->refresh();
    }

    private function bookFor(User $author, string $title = 'A Book'): Book
    {
        return $author->authorProfile->books()->create(['title' => $title, 'language' => 'en']);
    }

    private function listingFor(Book $book, bool $closed = false): BetaReadingListing
    {
        return $book->betaReadingListings()->create([
            'reading_brief' => 'A draft ready for feedback.',
            'word_count' => 80000,
            'closed_at' => $closed ? now() : null,
        ]);
    }

    private function interestFor(BetaReadingListing $listing, string $handle, mixed $createdAt = null): BetaReadingListingDecision
    {
        $reader = User::factory()->create();
        $profile = $reader->betaReaderProfile()->create([
            'display_name' => ucfirst(str_replace('-', ' ', $handle)),
            'handle' => $handle,
            'bio' => 'Current profile bio.',
            'reading_languages' => ['*'],
        ]);

        $interest = $profile->decisions()->create([
            'beta_reading_listing_id' => $listing->id,
            'decision' => BetaReadingListingDecision::INTERESTED,
        ]);

        if ($createdAt !== null) {
            $interest->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        return $interest;
    }
}
