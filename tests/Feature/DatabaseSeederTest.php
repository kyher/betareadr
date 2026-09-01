<?php

namespace Tests\Feature;

use App\Models\BetaReadingListing;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_reader_and_feed_content(): void
    {
        $existingReader = User::factory()->create([
            'name' => 'Existing Reader',
            'email' => 'test@example.com',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 5);
        $this->assertDatabaseCount('author_profiles', 4);
        $this->assertDatabaseCount('beta_reader_profiles', 1);
        $this->assertDatabaseCount('books', 8);
        $this->assertDatabaseCount('beta_reading_listings', 8);

        $reader = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($reader->is($existingReader));
        $this->assertSame('Existing Reader', $reader->name);
        $this->assertTrue(Hash::check('password', $reader->password));
        $this->assertSame('test-reader', $reader->betaReaderProfile?->handle);
        $this->assertSame(7, BetaReadingListing::query()->whereNull('closed_at')->count());
        $this->assertSame(1, BetaReadingListing::query()->whereNotNull('closed_at')->count());
    }
}
