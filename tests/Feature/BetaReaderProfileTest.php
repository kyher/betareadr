<?php

namespace Tests\Feature;

use App\Actions\BetaReaderProfiles\CreateBetaReaderProfile;
use App\Models\BetaReaderProfile;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class BetaReaderProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_beta_reader_profile_setup(): void
    {
        $this->get(route('beta-reader-profile.create'))->assertRedirect(route('login'));
        $this->post(route('beta-reader-profile.store'))->assertRedirect(route('login'));
        $this->get(route('beta-reader-profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('beta-reader-profile.update'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_a_beta_reader_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('beta-reader-profile.store'), [
            'display_name' => 'Keen Reader',
            'handle' => 'keen-reader',
            'bio' => "Science fiction fan.\nThoughtful critic.",
            'reading_languages' => ['en', 'fr'],
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('beta_reader_profiles', [
            'user_id' => $user->id,
            'display_name' => 'Keen Reader',
            'handle' => 'keen-reader',
        ]);
        $this->assertSame(['en', 'fr'], $user->betaReaderProfile->reading_languages);
    }

    public function test_profile_fields_are_validated(): void
    {
        $existingUser = User::factory()->create();
        $existingUser->betaReaderProfile()->create([
            'display_name' => 'Existing Reader',
            'handle' => 'existing-reader',
            'reading_languages' => ['*'],
        ]);
        $user = User::factory()->create();

        foreach (['UPPERCASE', '-leading', 'trailing-', 'double--hyphen', 'admin', 'existing-reader'] as $handle) {
            $this->actingAs($user)
                ->post(route('beta-reader-profile.store'), [
                    'display_name' => 'New Reader',
                    'handle' => $handle,
                    'reading_languages' => ['en'],
                ])
                ->assertSessionHasErrors('handle');
        }

        $this->actingAs($user)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'A',
                'handle' => 'valid-handle',
                'bio' => str_repeat('a', 1001),
                'reading_languages' => ['en'],
            ])
            ->assertSessionHasErrors(['display_name', 'bio']);

        $this->actingAs($user)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'New Reader',
                'handle' => 'valid-handle',
                'reading_languages' => ['*', 'en'],
            ])
            ->assertSessionHasErrors('reading_languages');
        $this->assertNull($user->betaReaderProfile);
    }

    public function test_author_and_beta_reader_handles_have_separate_namespaces(): void
    {
        $author = User::factory()->create();
        $author->authorProfile()->create([
            'display_name' => 'An Author',
            'handle' => 'shared-handle',
        ]);
        $reader = User::factory()->create();

        $this->actingAs($reader)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'A Reader',
                'handle' => 'shared-handle',
                'reading_languages' => ['en'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('beta_reader_profiles', ['handle' => 'shared-handle']);
    }

    public function test_user_cannot_create_a_second_beta_reader_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->betaReaderProfile()->create([
            'display_name' => 'First Reader',
            'handle' => 'first-reader',
            'reading_languages' => ['*'],
        ]);

        $this->actingAs($user)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'Second Reader',
                'handle' => 'second-reader',
                'reading_languages' => ['en'],
            ])
            ->assertRedirect(route('beta-reader-profile.edit'));

        $this->assertSame(1, BetaReaderProfile::query()->count());
        $this->assertSame('first-reader', $profile->fresh()->handle);
    }

    public function test_concurrent_handle_claim_returns_a_validation_error(): void
    {
        $user = User::factory()->create();
        $exception = new UniqueConstraintViolationException(
            'testing',
            'insert into beta_reader_profiles',
            [],
            new RuntimeException('Unique constraint violation'),
        );
        $exception->setColumns(['handle']);

        $this->mock(CreateBetaReaderProfile::class)
            ->shouldReceive('handle')->once()->andThrow($exception);

        $this->actingAs($user)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'New Reader',
                'handle' => 'claimed-handle',
                'reading_languages' => ['en'],
            ])
            ->assertSessionHasErrors('handle');
    }

    public function test_concurrent_profile_creation_redirects_to_edit(): void
    {
        $user = User::factory()->create();
        $exception = new UniqueConstraintViolationException(
            'testing',
            'insert into beta_reader_profiles',
            [],
            new RuntimeException('Unique constraint violation'),
        );
        $exception->setColumns(['user_id']);

        $this->mock(CreateBetaReaderProfile::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturnUsing(function (User $user) use ($exception): never {
                $user->betaReaderProfile()->create([
                    'display_name' => 'Created Elsewhere',
                    'handle' => 'created-elsewhere',
                    'reading_languages' => ['*'],
                ]);

                throw $exception;
            });

        $this->actingAs($user)
            ->post(route('beta-reader-profile.store'), [
                'display_name' => 'New Reader',
                'handle' => 'new-reader',
                'reading_languages' => ['en'],
            ])
            ->assertRedirect(route('beta-reader-profile.edit'));
    }

    public function test_setup_routes_redirect_according_to_profile_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('beta-reader-profile.edit'))
            ->assertRedirect(route('beta-reader-profile.create'));

        $user->betaReaderProfile()->create([
            'display_name' => 'A Reader',
            'handle' => 'a-reader',
            'reading_languages' => ['*'],
        ]);

        $this->actingAs($user)->get(route('beta-reader-profile.create'))
            ->assertRedirect(route('beta-reader-profile.edit'));
    }

    public function test_user_can_update_display_name_and_bio_but_not_handle(): void
    {
        $user = User::factory()->create();
        $profile = $user->betaReaderProfile()->create([
            'display_name' => 'Old Name',
            'handle' => 'fixed-handle',
            'reading_languages' => ['*'],
        ]);

        $this->actingAs($user)
            ->patch(route('beta-reader-profile.update'), [
                'display_name' => 'New Name',
                'handle' => 'changed-handle',
                'bio' => 'A new bio.',
                'reading_languages' => ['es'],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $profile->refresh();
        $this->assertSame('New Name', $profile->display_name);
        $this->assertSame('fixed-handle', $profile->handle);
        $this->assertSame('A new bio.', $profile->bio);
        $this->assertSame(['es'], $profile->reading_languages);
    }

    public function test_profile_is_public_without_user_or_author_details(): void
    {
        $user = User::factory()->create(['email' => 'private@example.com']);
        $user->authorProfile()->create([
            'display_name' => 'Separate Author',
            'handle' => 'separate-author',
        ]);
        $profile = $user->betaReaderProfile()->create([
            'display_name' => 'Public Reader',
            'handle' => 'public-reader',
            'bio' => 'Public bio.',
            'reading_languages' => ['*'],
        ]);

        $this->get(route('beta-readers.show', $profile))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('beta-reader-profiles/Show')
                ->where('betaReaderProfile.display_name', 'Public Reader')
                ->where('betaReaderProfile.handle', 'public-reader')
                ->where('betaReaderProfile.bio', 'Public bio.')
                ->where('betaReaderProfile.reading_languages', ['*'])
                ->missing('betaReaderProfile.user')
                ->missing('betaReaderProfile.email')
                ->missing('authorProfile'));
    }

    public function test_deleting_user_cascades_to_beta_reader_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->betaReaderProfile()->create([
            'display_name' => 'Deleted Reader',
            'handle' => 'deleted-reader',
            'reading_languages' => ['*'],
        ]);

        $user->delete();

        $this->assertDatabaseMissing('beta_reader_profiles', ['id' => $profile->id]);
    }
}
