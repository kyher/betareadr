<?php

namespace Tests\Feature;

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
