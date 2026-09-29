<?php

namespace Tests\Feature;

use App\Actions\BetaReadingListings\ReviewInterest;
use App\Enums\BetaReadingListingAuthorDecision;
use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\Administrator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_log_in_and_see_dashboard_counts(): void
    {
        $administrator = Administrator::create([
            'name' => 'Site Operator',
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $user = User::factory()->create();
        $author = $user->authorProfile()->create([
            'display_name' => 'Author',
            'handle' => 'author',
        ]);
        $user->betaReaderProfile()->create([
            'display_name' => 'Reader',
            'handle' => 'reader',
            'reading_languages' => ['en'],
        ]);
        $book = $author->books()->create([
            'title' => 'A Book',
            'description' => 'A description',
            'language' => 'en',
            'genre' => 'other',
        ]);
        $book->betaReadingListings()->create([
            'reading_brief' => 'Please review this book.',
            'word_count' => 80000,
        ]);
        $book->betaReadingListings()->create([
            'reading_brief' => 'This listing is closed.',
            'word_count' => 80000,
            'closed_at' => now(),
        ]);

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/login', [
            'email' => 'ADMIN@example.com',
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->where('administrator.name', 'Site Operator')
            ->where('counts', [
                'users' => 1,
                'authorProfiles' => 1,
                'betaReaderProfiles' => 1,
                'books' => 1,
                'openListings' => 1,
                'listings' => 2,
            ]));

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest('admin');
        $this->assertDatabaseHas('administrators', ['id' => $administrator->id]);
    }

    public function test_admin_uses_a_separate_scoped_session_cookie(): void
    {
        $response = $this->withCookie('locale', 'fr')->get('/admin/login');
        $cookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie').'_admin');

        $this->assertNotNull($cookie);
        $this->assertSame('/admin', $cookie->getPath());
        $this->assertSame('en', app()->getLocale());
    }

    public function test_administrator_can_review_and_search_paginated_records(): void
    {
        $administrator = Administrator::create([
            'name' => 'Operator', 'email' => 'operator@example.com', 'password' => 'password',
        ]);
        $user = User::factory()->create(['name' => 'Specific User', 'email' => 'specific@example.com']);
        $author = $user->authorProfile()->create(['display_name' => 'Specific Author', 'handle' => 'specific-author']);
        $user->betaReaderProfile()->create([
            'display_name' => 'Specific Reader', 'handle' => 'specific-reader',
            'reading_languages' => ['en'], 'content_boundaries' => ['violence'],
        ]);
        $book = $author->books()->create([
            'title' => 'Specific Book', 'description' => 'Review this description', 'language' => 'en', 'genre' => 'other',
        ]);
        $listing = $book->betaReadingListings()->create([
            'reading_brief' => 'Review this brief', 'word_count' => 80000,
        ]);
        $closed = $book->betaReadingListings()->create([
            'reading_brief' => 'Closed brief', 'word_count' => 90000, 'closed_at' => now(),
        ]);
        User::factory()->count(12)->create();

        foreach (['users', 'author-profiles', 'beta-reader-profiles', 'books', 'listings'] as $path) {
            $this->get("/admin/{$path}")->assertRedirect('/admin/login');
            $this->actingAs($user)->get("/admin/{$path}")->assertRedirect('/admin/login');
        }

        $this->actingAs($administrator, 'admin');
        $this->get('/admin/users')->assertInertia(fn (Assert $page) => $page
            ->component('admin/Index')->where('administrator.name', 'Operator')
            ->where('type', 'users')->has('records.data', 12)
            ->where('records.last_page', 2));
        $this->get('/admin/users?q=specific%20user')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->where('records.data.0.name', 'Specific User')
            ->missing('records.data.0.password'));
        $this->get('/admin/author-profiles?q=specific%20author')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->where('records.data.0.user', 'Specific User'));
        $this->get('/admin/beta-reader-profiles?q=specific%20reader')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->missing('records.data.0.content_boundaries'));
        $this->get('/admin/books?q=specific%20book')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->where('records.data.0.description', 'Review this description'));
        $this->get('/admin/listings?q=SPECIFIC-AUTHOR')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 2));
        $this->get("/admin/listings?q={$listing->id}&status=open")->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->where('records.data.0.id', $listing->id)
            ->where('records.data.0.reading_brief', 'Review this brief'));
        $this->get('/admin/listings?status=closed')->assertInertia(fn (Assert $page) => $page
            ->where('records.total', 1)->where('records.data.0.id', $closed->id));
    }

    public function test_administrator_can_remove_a_user(): void
    {
        $administrator = Administrator::create([
            'name' => 'Operator', 'email' => 'operator@example.com', 'password' => 'password',
        ]);
        $user = User::factory()->create();

        $this->delete("/admin/users/{$user->id}")->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())
            ->delete("/admin/users/{$user->id}")->assertRedirect('/admin/login');

        $this->actingAs($administrator, 'admin')
            ->delete("/admin/users/{$user->id}")->assertRedirect();

        $this->assertModelMissing($user);
        $this->assertAuthenticatedAs($administrator, 'admin');
    }

    public function test_administrator_can_remove_books_and_listings(): void
    {
        $administrator = Administrator::create(['name' => 'Operator', 'email' => 'operator@example.com', 'password' => 'password']);
        $author = User::factory()->create()->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author']);
        $book = $author->books()->create(['title' => 'Book to remove', 'language' => 'en', 'genre' => 'other']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Brief', 'word_count' => 1000]);
        $interest = User::factory()->create()->betaReaderProfile()->create([
            'display_name' => 'Reader', 'handle' => 'reader', 'reading_languages' => ['*'],
        ])->readerDecisions()->create([
            'beta_reading_listing_id' => $listing->id, 'reader_decision' => BetaReadingListingReaderDecisionType::Interested,
        ]);
        app(ReviewInterest::class)->handle($interest, BetaReadingListingAuthorDecision::Approved);
        $match = $interest->refresh()->match;
        $match->update(['ended_at' => now()]);

        $this->delete("/admin/books/{$book->id}")->assertRedirect('/admin/login');
        $this->actingAs($administrator, 'admin')->delete("/admin/listings/{$listing->id}")->assertRedirect();
        $this->assertModelMissing($listing);
        $this->assertModelMissing($interest);
        $this->assertModelMissing($match);

        $secondBook = $author->books()->create(['title' => 'Second book', 'language' => 'en', 'genre' => 'other']);
        $secondListing = $secondBook->betaReadingListings()->create(['reading_brief' => 'Brief', 'word_count' => 1000]);
        $this->delete("/admin/books/{$secondBook->id}")->assertRedirect();
        $this->assertModelMissing($secondBook);
        $this->assertModelMissing($secondListing);
    }

    public function test_administrator_cannot_remove_a_book_or_listing_with_an_active_match(): void
    {
        $administrator = Administrator::create(['name' => 'Operator', 'email' => 'operator@example.com', 'password' => 'password']);
        $author = User::factory()->create()->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author']);
        $book = $author->books()->create(['title' => 'Active book', 'language' => 'en', 'genre' => 'other']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Brief', 'word_count' => 1000]);
        $interest = User::factory()->create()->betaReaderProfile()->create([
            'display_name' => 'Reader', 'handle' => 'reader', 'reading_languages' => ['*'],
        ])->readerDecisions()->create([
            'beta_reading_listing_id' => $listing->id, 'reader_decision' => BetaReadingListingReaderDecisionType::Interested,
        ]);
        app(ReviewInterest::class)->handle($interest, BetaReadingListingAuthorDecision::Approved);

        $this->delete("/admin/books/{$book->id}")->assertRedirect('/admin/login');
        $this->actingAs($administrator, 'admin')->delete("/admin/books/{$book->id}")->assertSessionHasErrors('book');
        $this->actingAs(User::factory()->create())->delete("/admin/listings/{$listing->id}")->assertRedirect();
        $this->actingAs($administrator, 'admin')->delete("/admin/listings/{$listing->id}")->assertSessionHasErrors('listing');
        $this->assertModelExists($book);
        $this->assertModelExists($listing);
        $this->assertModelExists($interest->match);
    }

    public function test_administrator_commands_manage_credentials(): void
    {
        $this->artisan('admin:create ADMIN@example.com')
            ->expectsQuestion('Name', 'Site Operator')
            ->expectsQuestion('Password', 'password')
            ->expectsQuestion('Confirm password', 'password')
            ->assertSuccessful();

        $administrator = Administrator::firstOrFail();
        $this->assertSame('admin@example.com', $administrator->email);

        $this->artisan('admin:reset-password ADMIN@example.com')
            ->expectsQuestion('Password', 'new-password')
            ->expectsQuestion('Confirm password', 'new-password')
            ->assertSuccessful();
        $this->assertTrue(Hash::check('new-password', $administrator->refresh()->password));

        $this->artisan('admin:delete ADMIN@example.com')
            ->expectsConfirmation('Delete administrator admin@example.com?', 'yes')
            ->assertSuccessful();
        $this->assertDatabaseEmpty('administrators');
    }
}
