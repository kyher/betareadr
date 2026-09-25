<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserDeletionMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_retention_removal_migration_purges_detached_history_and_preserves_normal_matches(): void
    {
        $migration = require database_path('migrations/2026_09_18_000000_remove_account_deletion_retention.php');
        $migration->down();
        [$detached, $author, $reader] = $this->match('-detached');
        [$normal, $normalAuthor] = $this->match('-normal');
        $detached->messages()->create(['sender_id' => $author->id, 'body' => 'Detached author']);
        $detached->messages()->create(['sender_id' => $reader->id, 'body' => 'Detached reader']);
        $normal->messages()->create(['sender_id' => $normalAuthor->id, 'body' => 'Kept']);
        $detached->forceFill([
            'beta_reading_listing_decision_id' => null,
            'remaining_user_id' => $reader->id,
            'remaining_role' => 'beta_reader',
            'retained_book_title' => 'Deleted Book',
        ])->save();
        $author->delete();

        $migration->up();

        $this->assertNull($detached->fresh());
        $this->assertNotNull($normal->fresh());
        $this->assertDatabaseCount('conversation_messages', 1);
        $this->assertDatabaseHas('conversation_messages', ['beta_reading_match_id' => $normal->id, 'body' => 'Kept']);
        $this->assertFalse(Schema::hasColumn('beta_reading_matches', 'remaining_user_id'));
        $normalAuthor->delete();
        $this->assertNull($normal->fresh());
        $this->assertDatabaseCount('conversation_messages', 0);
    }

    private function match(string $suffix = ''): array
    {
        $author = User::factory()->create();
        $authorProfile = $author->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author'.$suffix]);
        $book = $authorProfile->books()->create(['title' => 'Book', 'language' => 'en', 'genre' => 'other']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Draft', 'word_count' => 100]);
        $reader = User::factory()->create();
        $profile = $reader->betaReaderProfile()->create(['display_name' => 'Reader', 'handle' => 'reader'.$suffix, 'reading_languages' => ['*']]);
        $decision = $profile->readerDecisions()->create(['beta_reading_listing_id' => $listing->id, 'reader_decision' => 'interested', 'author_decision' => 'approved']);

        return [$decision->match()->create([]), $author, $reader];
    }
}
