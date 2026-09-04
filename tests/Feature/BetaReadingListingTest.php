<?php

namespace Tests\Feature;

use App\Models\BetaReadingListing;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BetaReadingListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_manage_listings(): void
    {
        $book = $this->bookFor($this->author());
        $listing = $this->listingFor($book, closed: true);

        $this->get(route('books.listings.index', $book))->assertRedirect(route('login'));
        $this->get(route('books.listings.create', $book))->assertRedirect(route('login'));
        $this->post(route('books.listings.store', $book))->assertRedirect(route('login'));
        $this->get(route('books.listings.edit', [$book, $listing]))->assertRedirect(route('login'));
        $this->patch(route('books.listings.update', [$book, $listing]))->assertRedirect(route('login'));
        $this->patch(route('books.listings.close', [$book, $listing]))->assertRedirect(route('login'));
        $this->patch(route('books.listings.reopen', [$book, $listing]))->assertRedirect(route('login'));
    }

    public function test_author_can_create_an_immediately_open_listing(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);

        $this->actingAs($user)->post(route('books.listings.store', $book), [
            'reading_brief' => '  This is the second draft. Focus on pacing.  ',
            'word_count' => '82000',
        ])->assertSessionHasNoErrors()->assertRedirect(route('books.listings.index', $book));

        $this->assertDatabaseHas('beta_reading_listings', [
            'book_id' => $book->id,
            'reading_brief' => 'This is the second draft. Focus on pacing.',
            'word_count' => 82000,
            'closed_at' => null,
        ]);
    }

    public function test_listing_fields_are_validated(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);

        $this->actingAs($user)->post(route('books.listings.store', $book), [
            'reading_brief' => str_repeat('a', 2001),
            'word_count' => 2000001,
        ])->assertSessionHasErrors(['reading_brief', 'word_count']);

        $this->actingAs($user)->post(route('books.listings.store', $book), [
            'reading_brief' => '',
            'word_count' => 0,
        ])->assertSessionHasErrors(['reading_brief', 'word_count']);
    }

    public function test_only_one_listing_per_book_can_be_open(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $this->listingFor($book);

        $this->actingAs($user)->get(route('books.listings.create', $book))
            ->assertRedirect(route('books.listings.index', $book))
            ->assertSessionHasErrors('listing');

        $this->actingAs($user)->post(route('books.listings.store', $book), [
            'reading_brief' => 'Another round',
            'word_count' => 90000,
        ])->assertSessionHasErrors('listing');

        $this->assertSame(1, $book->betaReadingListings()->count());
    }

    public function test_management_page_pins_open_listing_then_orders_closed_listings_newest_first(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $open = $this->listingFor($book);
        $newerClosed = $this->listingFor($book, 'Newer closed', true);
        $olderClosed = $this->listingFor($book, 'Older closed', true);

        $open->forceFill(['created_at' => now()->subDays(3)])->save();
        $olderClosed->forceFill(['created_at' => now()->subDays(2)])->save();
        $newerClosed->forceFill(['created_at' => now()->subDay()])->save();

        $this->actingAs($user)->get(route('books.listings.index', $book))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('beta-reading-listings/Index')
                ->where('book.id', $book->id)
                ->where('hasOpenListing', true)
                ->has('listings.data', 3)
                ->where('listings.data.0.id', $open->id)
                ->where('listings.data.1.id', $newerClosed->id)
                ->where('listings.data.2.id', $olderClosed->id)
                ->where('listings.per_page', 12));
    }

    public function test_listing_history_is_paginated_with_id_as_timestamp_tiebreaker(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $createdAt = now();
        $listings = collect(range(1, 13))->map(fn (int $number): BetaReadingListing => $this->listingFor(
            $book,
            "Round {$number}",
            true,
            $createdAt,
        ));

        $this->actingAs($user)->get(route('books.listings.index', $book))
            ->assertInertia(fn (Assert $page) => $page
                ->where('listings.data.0.id', $listings->last()->id)
                ->where('listings.data.11.id', $listings->get(1)->id));

        $this->actingAs($user)->get(route('books.listings.index', [$book, 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('listings.data', 1)
                ->where('listings.data.0.id', $listings->first()->id));
    }

    public function test_author_can_edit_open_or_closed_listings(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $open = $this->listingFor($book);
        $closed = $this->listingFor($book, 'Closed round', true);

        foreach ([$open, $closed] as $listing) {
            $this->actingAs($user)->patch(route('books.listings.update', [$book, $listing]), [
                'reading_brief' => '  Revised brief  ',
                'word_count' => 71000,
            ])->assertSessionHasNoErrors()->assertRedirect(route('books.listings.index', $book));

            $this->assertDatabaseHas('beta_reading_listings', [
                'id' => $listing->id,
                'reading_brief' => 'Revised brief',
                'word_count' => 71000,
            ]);
        }
    }

    public function test_author_can_close_and_reopen_a_listing(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $listing = $this->listingFor($book);

        $this->actingAs($user)->patch(route('books.listings.close', [$book, $listing]))
            ->assertSessionHasNoErrors()->assertRedirect(route('books.listings.index', $book));
        $this->assertNotNull($listing->refresh()->closed_at);

        $this->actingAs($user)->patch(route('books.listings.reopen', [$book, $listing]))
            ->assertSessionHasNoErrors()->assertRedirect(route('books.listings.index', $book));
        $this->assertNull($listing->refresh()->closed_at);
    }

    public function test_author_cannot_reopen_an_old_listing_while_another_is_open(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);
        $open = $this->listingFor($book);
        $closed = $this->listingFor($book, 'Old round', true);

        $this->actingAs($user)
            ->from(route('books.listings.index', $book))
            ->patch(route('books.listings.reopen', [$book, $closed]))
            ->assertSessionHasErrors('listing')
            ->assertRedirect(route('books.listings.index', $book));

        $this->assertNull($open->refresh()->closed_at);
        $this->assertNotNull($closed->refresh()->closed_at);
    }

    public function test_author_cannot_manage_another_authors_listings(): void
    {
        $owner = $this->author();
        $other = $this->author('other-author');
        $book = $this->bookFor($owner);
        $listing = $this->listingFor($book);

        $this->actingAs($other)->get(route('books.listings.index', $book))->assertForbidden();
        $this->actingAs($other)->get(route('books.listings.create', $book))->assertForbidden();
        $this->actingAs($other)->post(route('books.listings.store', $book), [
            'reading_brief' => 'Stolen',
            'word_count' => 100,
        ])->assertForbidden();
        $this->actingAs($other)->get(route('books.listings.edit', [$book, $listing]))->assertForbidden();
        $this->actingAs($other)->patch(route('books.listings.update', [$book, $listing]), [
            'reading_brief' => 'Stolen',
            'word_count' => 100,
        ])->assertForbidden();
        $this->actingAs($other)->patch(route('books.listings.close', [$book, $listing]))->assertForbidden();
        $this->actingAs($other)->patch(route('books.listings.reopen', [$book, $listing]))->assertForbidden();
    }

    public function test_listing_must_belong_to_the_book_in_the_url(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user, 'First Book');
        $otherBook = $this->bookFor($user, 'Second Book');
        $listing = $this->listingFor($book);

        $this->actingAs($user)->get(route('books.listings.edit', [$otherBook, $listing]))->assertNotFound();
        $this->actingAs($user)->patch(route('books.listings.close', [$otherBook, $listing]))->assertNotFound();
    }

    public function test_deleting_a_book_cascades_to_its_listings(): void
    {
        $book = $this->bookFor($this->author());
        $listing = $this->listingFor($book);

        $book->delete();

        $this->assertDatabaseMissing('beta_reading_listings', ['id' => $listing->id]);
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

    private function bookFor(User $user, string $title = 'A Book'): Book
    {
        return $user->authorProfile->books()->create(['title' => $title, 'language' => 'en']);
    }

    private function listingFor(
        Book $book,
        string $brief = 'A draft ready for feedback.',
        bool $closed = false,
        mixed $createdAt = null,
    ): BetaReadingListing {
        $listing = $book->betaReadingListings()->create([
            'reading_brief' => $brief,
            'word_count' => 80000,
            'closed_at' => $closed ? now() : null,
        ]);

        if ($createdAt !== null) {
            $listing->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();
        }

        return $listing;
    }
}
