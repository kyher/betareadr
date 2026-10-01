<?php

namespace Tests\Feature;

use App\Actions\Profiles\SaveProfile;
use App\Actions\Users\DeleteUser;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public static function profiles(): array
    {
        return [
            'author' => ['author-profile', 'authorProfile', 'authors.show'],
            'beta reader' => ['beta-reader-profile', 'betaReaderProfile', 'beta-readers.show'],
        ];
    }

    #[DataProvider('profiles')]
    public function test_avatar_can_be_added_preserved_replaced_and_removed(string $route, string $relation, string $publicRoute): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $attributes = ['display_name' => 'Public Profile', 'handle' => 'public-profile', 'reading_languages' => ['*']];
        $this->actingAs($user)->post(route($route.'.store'), [
            ...$attributes, 'avatar' => UploadedFile::fake()->create('avatar.png', 1, 'image/png'),
        ])->assertSessionHasNoErrors();
        $profile = $user->$relation;
        $path = $profile->avatar_path;
        $this->assertStringStartsWith('profile-avatars/', $path);
        Storage::disk('r2')->assertExists($path);
        $url = Storage::disk('r2')->url($path);

        foreach ([$publicRoute, $route.'.edit', 'dashboard'] as $pageRoute) {
            $this->get(route($pageRoute, $pageRoute === $publicRoute ? $profile : []))
                ->assertInertia(fn (Assert $page) => $page
                    ->where($relation.'.avatar_url', $url)
                    ->missing($relation.'.avatar_path'));
        }

        $this->patch(route($route.'.update'), $attributes)->assertSessionHasNoErrors();
        $this->assertSame($path, $profile->fresh()->avatar_path);
        $this->patch(route($route.'.update'), [...$attributes, 'avatar' => UploadedFile::fake()->create('bad.txt', 1, 'text/plain')])
            ->assertSessionHasErrors('avatar');
        $this->assertSame($path, $profile->fresh()->avatar_path);
        Storage::disk('r2')->assertExists($path);

        // Exercise the POST method spoof used by the browser's multipart form.
        $this->post(route($route.'.update'), [
            ...$attributes, '_method' => 'PATCH', 'remove_avatar' => '1',
            'avatar' => UploadedFile::fake()->create('replacement.webp', 1, 'image/webp'),
        ])->assertSessionHasNoErrors();
        $newPath = $profile->fresh()->avatar_path;
        $this->assertNotNull($newPath);
        $this->assertNotSame($path, $newPath);
        Storage::disk('r2')->assertMissing($path);
        Storage::disk('r2')->assertExists($newPath);

        $this->patch(route($route.'.update'), [...$attributes, 'remove_avatar' => '1'])->assertSessionHasNoErrors();
        $this->assertNull($profile->fresh()->avatar_path);
        Storage::disk('r2')->assertMissing($newPath);

        $this->post(route($route.'.update'), [
            ...$attributes, '_method' => 'PATCH', 'avatar' => UploadedFile::fake()->create('added.jpg', 1, 'image/jpeg'),
        ])->assertSessionHasNoErrors();
        Storage::disk('r2')->assertExists($profile->fresh()->avatar_path);
    }

    #[DataProvider('profiles')]
    public function test_avatars_validate_type_and_size_and_ignore_client_storage_paths(string $route, string $relation, string $publicRoute): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $attributes = ['display_name' => 'Public Profile', 'handle' => 'public-profile', 'reading_languages' => ['*']];
        foreach ([
            UploadedFile::fake()->create('bad.txt', 1, 'text/plain'),
            UploadedFile::fake()->create('bad.gif', 1, 'image/gif'),
            UploadedFile::fake()->create('large.png', 5121, 'image/png'),
        ] as $avatar) {
            $this->actingAs($user)->post(route($route.'.store'), [...$attributes, 'avatar' => $avatar])
                ->assertSessionHasErrors('avatar');
        }
        $this->assertSame([], Storage::disk('r2')->allFiles());
        $this->post(route($route.'.store'), [...$attributes, 'avatar_path' => 'profile-avatars/another-user.jpg'])
            ->assertSessionHasNoErrors();
        $this->assertNull($user->$relation->avatar_path);
        $this->get(route($publicRoute, $user->$relation))
            ->assertInertia(fn (Assert $page) => $page->where($relation.'.avatar_url', null));
    }

    public function test_failed_database_saves_clean_new_uploads_and_preserve_existing_avatar(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $profile = $user->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author', 'avatar_path' => 'profile-avatars/old.jpg']);
        Storage::disk('r2')->put($profile->avatar_path, 'old avatar');
        $saveProfile = app(SaveProfile::class);

        foreach ([$profile, $user->authorProfile()->make()] as $record) {
            try {
                $saveProfile->handle($record, ['display_name' => null], UploadedFile::fake()->create('new.png', 1, 'image/png'));
                $this->fail('Expected the database save to fail.');
            } catch (QueryException) {
                $this->assertSame('profile-avatars/old.jpg', $profile->fresh()->avatar_path);
                $this->assertSame(['profile-avatars/old.jpg'], Storage::disk('r2')->allFiles());
            }
        }
    }

    public function test_profiles_have_independent_avatars_and_user_deletion_cleans_both(): void
    {
        Storage::fake('r2');
        $user = User::factory()->create();
        $author = $user->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author', 'avatar_path' => 'profile-avatars/author.jpg']);
        $reader = $user->betaReaderProfile()->create(['display_name' => 'Reader', 'handle' => 'reader', 'reading_languages' => ['*'], 'avatar_path' => 'profile-avatars/reader.jpg']);
        foreach ([$author->avatar_path, $reader->avatar_path, 'profile-avatars/unrelated.jpg'] as $path) {
            Storage::disk('r2')->put($path, 'avatar');
        }
        $this->actingAs($user)->patch(route('author-profile.update'), ['display_name' => 'Author', 'remove_avatar' => '1'])
            ->assertSessionHasNoErrors();
        $this->assertSame('profile-avatars/reader.jpg', $reader->fresh()->avatar_path);
        Storage::disk('r2')->assertExists($reader->avatar_path);
        $author->update(['avatar_path' => 'profile-avatars/author-new.jpg']);
        Storage::disk('r2')->put($author->avatar_path, 'avatar');

        app(DeleteUser::class)->handle($user);
        Storage::disk('r2')->assertMissing($author->avatar_path);
        Storage::disk('r2')->assertMissing($reader->avatar_path);
        Storage::disk('r2')->assertExists('profile-avatars/unrelated.jpg');
    }

    public function test_discovery_and_connections_show_the_correct_profile_avatar(): void
    {
        Storage::fake('r2');
        $author = User::factory()->create();
        $authorProfile = $author->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author', 'avatar_path' => 'profile-avatars/author.jpg']);
        $reader = User::factory()->create();
        $readerProfile = $reader->betaReaderProfile()->create(['display_name' => 'Reader', 'handle' => 'reader', 'reading_languages' => ['*'], 'avatar_path' => 'profile-avatars/reader.jpg']);
        $book = $authorProfile->books()->create(['title' => 'Book', 'language' => 'en', 'genre' => 'fantasy']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Draft', 'word_count' => 100]);
        $this->actingAs($reader)->get(route('feed.index'))->assertInertia(fn (Assert $page) => $page->where('listing.author.avatar_url', $authorProfile->avatarUrl()));
        $interest = $readerProfile->readerDecisions()->create(['beta_reading_listing_id' => $listing->id, 'reader_decision' => 'interested']);
        $this->actingAs($author)->get(route('books.listings.readers.index', [$book, $listing]))
            ->assertInertia(fn (Assert $page) => $page->where('interested.0.profile.avatar_url', $readerProfile->avatarUrl()));
        $interest->update(['author_decision' => 'approved']);
        $match = $interest->match()->create([]);
        foreach ([[$author, $readerProfile], [$reader, $authorProfile]] as [$user, $otherProfile]) {
            $this->actingAs($user)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.profile.avatar_url', $otherProfile->avatarUrl()));
            $this->get(route('matches.conversation', $match))->assertInertia(fn (Assert $page) => $page->where('match.profile.avatar_url', $otherProfile->avatarUrl()));
        }
    }
}
