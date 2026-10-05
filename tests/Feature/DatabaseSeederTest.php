<?php

namespace Tests\Feature;

use App\Models\AuthorProfile;
use App\Models\BetaReaderProfile;
use App\Models\BetaReadingMatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_repeated_seeding_reuses_named_beta_reader_profiles(): void
    {
        $this->seed();

        $profiles = BetaReaderProfile::query()->pluck('user_id', 'handle')->all();

        $this->seed();

        $this->assertSame($profiles, BetaReaderProfile::query()->pluck('user_id', 'handle')->all());
        $this->assertDatabaseCount('beta_reader_profiles', 2);
        $this->assertDatabaseCount('users', 10);

        foreach (AuthorProfile::query()->pluck('handle') as $handle) {
            $this->assertLessThanOrEqual(30, strlen($handle));
        }
    }

    public function test_test_user_has_matches_in_both_roles(): void
    {
        $this->seed();

        $user = User::query()
            ->where('email', 'test@example.com')
            ->firstOrFail();
        $readerMatches = $user->betaReaderProfile
            ->readerDecisions()
            ->with('match')
            ->get()
            ->pluck('match');
        $authorMatches = BetaReadingMatch::query()
            ->whereHas(
                'readerDecision.betaReadingListing.book.authorProfile',
                fn ($query) => $query->where('user_id', $user->id),
            )
            ->get();

        $this->assertCount(2, $readerMatches);
        $this->assertCount(1, $readerMatches->whereNull('ended_at'));
        $this->assertCount(1, $readerMatches->whereNotNull('ended_at'));
        $this->assertCount(1, $authorMatches);
        $this->assertNull($authorMatches->first()->ended_at);
    }
}
