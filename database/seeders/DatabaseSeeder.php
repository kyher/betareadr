<?php

namespace Database\Seeders;

use App\Enums\BetaReadingListingAuthorDecision;
use App\Enums\BetaReadingListingReaderDecisionType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $reader = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $readerProfile = $reader->betaReaderProfile()->updateOrCreate(
            ['user_id' => $reader->id],
            [
                'display_name' => fake()->name(),
                'handle' => 'test-reader',
                'bio' => fake()->paragraph(),
                'reading_languages' => ['*'],
            ],
        );
        $authorProfile = $reader->authorProfile()->updateOrCreate(
            ['user_id' => $reader->id],
            [
                'display_name' => 'Test Author',
                'handle' => 'test-author',
                'bio' => fake()->paragraph(),
            ],
        );
        $testListing = $authorProfile->books()->create([
            'title' => 'The Test Manuscript',
            'description' => fake()->paragraphs(2, true),
            'language' => 'en',
            'genre' => 'other',
        ])->betaReadingListings()->create([
            'reading_brief' => 'Looking for feedback on character motivation and pacing.',
            'word_count' => 80000,
        ]);
        $matchedReader = User::factory()->create()->betaReaderProfile()->create([
            'display_name' => 'Matched Beta Reader',
            'handle' => 'matched-beta-reader',
            'bio' => fake()->paragraph(),
            'reading_languages' => ['*'],
        ]);
        $authorInterest = $matchedReader->readerDecisions()->create([
            'beta_reading_listing_id' => $testListing->id,
            'reader_decision' => BetaReadingListingReaderDecisionType::Interested,
            'author_decision' => BetaReadingListingAuthorDecision::Approved,
        ]);
        $authorInterest->match()->create();

        foreach (range(1, 4) as $authorNumber) {
            $user = User::factory()->create();
            $displayName = fake()->name();
            $author = $user->authorProfile()->create([
                'display_name' => $displayName,
                'handle' => "author-{$authorNumber}-".fake()->numerify('####'),
                'bio' => fake()->paragraph(),
            ]);

            foreach (range(1, 2) as $bookNumber) {
                $book = $author->books()->create([
                    'title' => fake()->sentence(3),
                    'description' => fake()->paragraphs(2, true),
                    'language' => 'en',
                    'genre' => 'other',
                ]);

                $listing = $book->betaReadingListings()->create([
                    'reading_brief' => fake()->paragraphs(2, true),
                    'word_count' => fake()->numberBetween(45000, 120000),
                    'closed_at' => $authorNumber === 1 && $bookNumber === 2
                        ? now()
                        : null,
                ]);

                if ($bookNumber === 1 && $authorNumber <= 2) {
                    $interest = $readerProfile->readerDecisions()->create([
                        'beta_reading_listing_id' => $listing->id,
                        'reader_decision' => BetaReadingListingReaderDecisionType::Interested,
                        'author_decision' => BetaReadingListingAuthorDecision::Approved,
                    ]);
                    $interest->match()->create([
                        'ended_at' => $authorNumber === 2 ? now() : null,
                    ]);
                }
            }
        }
    }
}
