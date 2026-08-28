<?php

namespace Tests\Feature;

use App\Actions\AuthorProfiles\CreateAuthorProfile;
use App\Models\AuthorProfile;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class AuthorProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_author_profile_setup(): void
    {
        $this->get(route('author-profile.create'))->assertRedirect(route('login'));
        $this->post(route('author-profile.store'))->assertRedirect(route('login'));
        $this->get(route('author-profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('author-profile.update'))->assertRedirect(route('login'));
    }

    public function test_user_can_create_an_author_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('author-profile.store'), [
            'display_name' => 'Ursula Le Guin',
            'handle' => 'ursula-le-guin',
            'bio' => "Author of Earthsea.\nLover of language.",
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('author_profiles', [
            'user_id' => $user->id,
            'display_name' => 'Ursula Le Guin',
            'handle' => 'ursula-le-guin',
        ]);
    }

    public function test_author_profile_requires_a_valid_unique_non_reserved_handle(): void
    {
        $existingUser = User::factory()->create();
        $existingUser->authorProfile()->create([
            'display_name' => 'Existing Author',
            'handle' => 'existing-author',
        ]);

        $user = User::factory()->create();

        foreach (['UPPERCASE', '-leading', 'trailing-', 'double--hyphen', 'admin', 'existing-author'] as $handle) {
            $this->actingAs($user)
                ->post(route('author-profile.store'), [
                    'display_name' => 'New Author',
                    'handle' => $handle,
                ])
                ->assertSessionHasErrors('handle');
        }

        $this->assertNull($user->authorProfile);
    }

    public function test_display_name_and_bio_are_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('author-profile.store'), [
                'display_name' => 'A',
                'handle' => 'valid-handle',
                'bio' => str_repeat('a', 1001),
            ])
            ->assertSessionHasErrors(['display_name', 'bio']);
    }

    public function test_user_cannot_create_a_second_author_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->authorProfile()->create([
            'display_name' => 'First Name',
            'handle' => 'first-name',
        ]);

        $this->actingAs($user)
            ->post(route('author-profile.store'), [
                'display_name' => 'Second Name',
                'handle' => 'second-name',
            ])
            ->assertRedirect(route('author-profile.edit'));

        $this->assertSame(1, AuthorProfile::query()->count());
        $this->assertSame('first-name', $profile->fresh()->handle);
    }

    public function test_concurrent_handle_claim_returns_a_validation_error(): void
    {
        $user = User::factory()->create();
        $exception = new UniqueConstraintViolationException(
            'testing',
            'insert into author_profiles',
            [],
            new RuntimeException('Unique constraint violation'),
        );
        $exception->setColumns(['handle']);

        $this->mock(CreateAuthorProfile::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow($exception);

        $this->actingAs($user)
            ->post(route('author-profile.store'), [
                'display_name' => 'New Author',
                'handle' => 'claimed-handle',
            ])
            ->assertSessionHasErrors('handle');
    }

    public function test_concurrent_profile_creation_redirects_to_edit(): void
    {
        $user = User::factory()->create();
        $exception = new UniqueConstraintViolationException(
            'testing',
            'insert into author_profiles',
            [],
            new RuntimeException('Unique constraint violation'),
        );
        $exception->setColumns(['user_id']);

        $this->mock(CreateAuthorProfile::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturnUsing(function (User $user) use ($exception): never {
                $user->authorProfile()->create([
                    'display_name' => 'Created Elsewhere',
                    'handle' => 'created-elsewhere',
                ]);

                throw $exception;
            });

        $this->actingAs($user)
            ->post(route('author-profile.store'), [
                'display_name' => 'New Author',
                'handle' => 'new-author',
            ])
            ->assertRedirect(route('author-profile.edit'));
    }

    public function test_setup_routes_redirect_according_to_profile_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('author-profile.edit'))
            ->assertRedirect(route('author-profile.create'));

        $user->authorProfile()->create([
            'display_name' => 'An Author',
            'handle' => 'an-author',
        ]);

        $this->actingAs($user)
            ->get(route('author-profile.create'))
            ->assertRedirect(route('author-profile.edit'));
    }

    public function test_user_can_update_display_name_and_bio_but_not_handle(): void
    {
        $user = User::factory()->create();
        $profile = $user->authorProfile()->create([
            'display_name' => 'Old Name',
            'handle' => 'fixed-handle',
        ]);

        $this->actingAs($user)
            ->patch(route('author-profile.update'), [
                'display_name' => 'New Name',
                'handle' => 'changed-handle',
                'bio' => 'A new bio.',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $profile->refresh();
        $this->assertSame('New Name', $profile->display_name);
        $this->assertSame('fixed-handle', $profile->handle);
        $this->assertSame('A new bio.', $profile->bio);
    }

    public function test_author_profile_is_publicly_visible_without_member_details(): void
    {
        $user = User::factory()->create(['email' => 'private@example.com']);
        $profile = $user->authorProfile()->create([
            'display_name' => 'Public Name',
            'handle' => 'public-name',
            'bio' => 'Public bio.',
        ]);

        $this->get(route('authors.show', $profile))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('author-profiles/Show')
                ->where('authorProfile.display_name', 'Public Name')
                ->where('authorProfile.handle', 'public-name')
                ->where('authorProfile.bio', 'Public bio.')
                ->missing('authorProfile.user')
                ->missing('authorProfile.email'));
    }

    public function test_deleting_user_cascades_to_author_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->authorProfile()->create([
            'display_name' => 'Deleted Author',
            'handle' => 'deleted-author',
        ]);

        $user->delete();

        $this->assertDatabaseMissing('author_profiles', ['id' => $profile->id]);
    }
}
