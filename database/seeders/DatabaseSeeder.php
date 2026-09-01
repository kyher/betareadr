<?php

namespace Database\Seeders;

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
        $reader->betaReaderProfile()->updateOrCreate(
            ['user_id' => $reader->id],
            [
                'display_name' => fake()->name(),
                'handle' => 'test-reader',
                'bio' => fake()->paragraph(),
            ],
        );

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
                ]);

                $book->betaReadingListings()->create([
                    'reading_brief' => fake()->paragraphs(2, true),
                    'word_count' => fake()->numberBetween(45000, 120000),
                    'closed_at' => $authorNumber === 1 && $bookNumber === 2
                        ? now()
                        : null,
                ]);
            }
        }
    }
}
