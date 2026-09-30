<?php

namespace Tests\Feature;

use App\Actions\Users\DeleteUser;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_warnings_are_saved_validated_and_public(): void
    {
        $user = $this->author();
        $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Warned Book', 'language' => 'en', 'genre' => 'horror', 'content_warnings' => ['abuse', 'violence'],
        ])->assertSessionHasNoErrors();
        $book = $user->authorProfile->books()->where('title', 'Warned Book')->firstOrFail();
        $this->assertSame(['abuse', 'violence'], $book->content_warnings);

        $this->get(route('authors.show', $user->authorProfile))
            ->assertInertia(fn (Assert $page) => $page
                ->where('books.data.0.genre', 'horror')
                ->where('books.data.0.content_warnings', ['abuse', 'violence']));

        $this->actingAs($user)->patch(route('books.update', $book), [
            'title' => 'Warned Book', 'language' => 'en', 'genre' => 'horror', 'content_warnings' => ['unknown'],
        ])->assertSessionHasErrors('content_warnings.0');

        $this->actingAs($user)->patch(route('books.update', $book), [
            'title' => 'Warned Book', 'language' => 'en', 'genre' => 'horror',
        ])->assertSessionHasNoErrors();
        $this->assertSame([], $book->fresh()->content_warnings);
    }

    public function test_guests_cannot_manage_books(): void
    {
        $book = $this->bookFor(User::factory()->create());

        $this->get(route('books.index'))->assertRedirect(route('login'));
        $this->get(route('books.create'))->assertRedirect(route('login'));
        $this->post(route('books.store'))->assertRedirect(route('login'));
        $this->get(route('books.edit', $book))->assertRedirect(route('login'));
        $this->patch(route('books.update', $book))->assertRedirect(route('login'));
        $this->delete(route('books.destroy', $book))->assertRedirect(route('login'));
    }

    public function test_user_without_author_profile_is_redirected_to_profile_setup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('books.index'))->assertRedirect(route('author-profile.create'));
        $this->actingAs($user)->get(route('books.create'))->assertRedirect(route('author-profile.create'));
        $this->actingAs($user)->post(route('books.store'), ['title' => 'A Book', 'language' => 'en', 'genre' => 'other'])
            ->assertRedirect(route('author-profile.create'));
    }

    public function test_author_can_add_a_book_with_an_optional_description(): void
    {
        $user = $this->author();

        $this->actingAs($user)->post(route('books.store'), [
            'title' => '  The Dispossessed  ',
            'description' => '  An ambiguous utopia.  ',
            'language' => 'en',
            'genre' => 'science_fiction',
        ])->assertSessionHasNoErrors()->assertRedirect(route('books.index'));

        $this->actingAs($user)->post(route('books.store'), [
            'title' => 'The Lathe of Heaven',
            'description' => '',
            'language' => 'fr',
            'genre' => 'science_fiction',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', [
            'author_profile_id' => $user->authorProfile->id,
            'title' => 'The Dispossessed',
            'description' => 'An ambiguous utopia.',
            'language' => 'en',
            'genre' => 'science_fiction',
        ]);
        $this->assertDatabaseHas('books', [
            'title' => 'The Lathe of Heaven',
            'description' => null,
            'language' => 'fr',
            'genre' => 'science_fiction',
        ]);
    }

    public function test_author_can_add_a_cover_and_it_is_exposed_on_their_public_profile(): void
    {
        Storage::fake('r2');
        $user = $this->author();

        $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Covered Book',
            'language' => 'en',
            'genre' => 'other',
            'cover_image' => UploadedFile::fake()->create('cover.png', 1, 'image/png'),
        ])->assertSessionHasNoErrors();

        $book = $user->authorProfile->books()->where('title', 'Covered Book')->firstOrFail();
        $coverPath = $book->cover_image_path;
        $this->assertNotNull($coverPath);
        Storage::disk('r2')->assertExists($coverPath);
        $coverUrl = Storage::disk('r2')->url($coverPath);

        $this->get(route('authors.show', $user->authorProfile))
            ->assertInertia(fn (Assert $page) => $page->where('books.data.0.cover_image_url', $coverUrl));
    }

    public function test_cover_images_are_limited_to_supported_types_and_five_megabytes(): void
    {
        Storage::fake('r2');
        $user = $this->author();

        foreach ([
            UploadedFile::fake()->create('cover.txt', 1, 'text/plain'),
            UploadedFile::fake()->create('large.png', 5121, 'image/png'),
        ] as $cover) {
            $this->actingAs($user)->post(route('books.store'), [
                'title' => 'Invalid Cover',
                'language' => 'en',
                'genre' => 'other',
                'cover_image' => $cover,
            ])->assertSessionHasErrors('cover_image');
        }

        $this->assertDatabaseMissing('books', ['title' => 'Invalid Cover']);
    }

    public function test_author_can_replace_and_remove_a_cover_image(): void
    {
        Storage::fake('r2');
        $user = $this->author();
        $book = $this->bookFor($user);
        $oldPath = 'book-covers/old-cover.jpg';
        Storage::disk('r2')->put($oldPath, 'old cover');
        $book->update(['cover_image_path' => $oldPath]);

        $this->actingAs($user)->patch(route('books.update', $book), [
            'title' => $book->title,
            'language' => 'en',
            'genre' => 'other',
            'cover_image' => UploadedFile::fake()->create('new-cover.png', 1, 'image/png'),
        ])->assertSessionHasNoErrors();

        $newPath = $book->fresh()->cover_image_path;
        $this->assertNotNull($newPath);
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('r2')->assertMissing($oldPath);
        Storage::disk('r2')->assertExists($newPath);

        $this->actingAs($user)->patch(route('books.update', $book), [
            'title' => $book->title,
            'language' => 'en',
            'genre' => 'other',
            'remove_cover_image' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($book->fresh()->cover_image_path);
        Storage::disk('r2')->assertMissing($newPath);
    }

    public function test_duplicate_titles_are_allowed_for_the_same_author(): void
    {
        $user = $this->author();

        $user->authorProfile->books()->create(['title' => 'Working Title', 'language' => 'en', 'genre' => 'other']);
        $this->actingAs($user)->post(route('books.store'), ['title' => 'Working Title', 'language' => 'en', 'genre' => 'other'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Book::query()->where('title', 'Working Title')->count());
    }

    public function test_book_fields_are_validated(): void
    {
        $user = $this->author();

        $this->actingAs($user)->post(route('books.store'), [
            'title' => str_repeat('a', 201),
            'description' => str_repeat('b', 2001),
            'language' => 'xx',
            'genre' => 'made_up',
        ])->assertSessionHasErrors(['title', 'description', 'language', 'genre']);

        $this->actingAs($user)->post(route('books.store'), ['title' => ''])
            ->assertSessionHasErrors('title');
        $this->actingAs($user)->post(route('books.store'), ['title' => 'A Book'])
            ->assertSessionHasErrors(['language', 'genre']);
    }

    public function test_author_sees_only_their_books_on_the_management_page_newest_first(): void
    {
        $user = $this->author();
        $other = $this->author('other-author');
        $older = $user->authorProfile->books()->create(['title' => 'Older', 'language' => 'en', 'genre' => 'other']);
        $newer = $user->authorProfile->books()->create(['title' => 'Newer', 'language' => 'en', 'genre' => 'other']);
        $older->update(['created_at' => now()->subDay()]);
        $other->authorProfile->books()->create(['title' => 'Private Management Data', 'language' => 'en', 'genre' => 'other']);

        $this->actingAs($user)->get(route('books.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('books/Index')
                ->has('books.data', 2)
                ->where('books.data.0.id', $newer->id)
                ->where('books.data.1.id', $older->id)
                ->where('books.per_page', 12));
    }

    public function test_author_can_edit_and_delete_their_book(): void
    {
        Storage::fake('r2');
        $user = $this->author();
        $book = $this->bookFor($user);
        $coverPath = 'book-covers/deleted-cover.jpg';
        Storage::disk('r2')->put($coverPath, 'cover');
        $book->update(['cover_image_path' => $coverPath]);

        $this->actingAs($user)->patch(route('books.update', $book), [
            'title' => 'Changed Title',
            'description' => 'Changed description.',
            'language' => 'es',
            'genre' => 'fantasy',
        ])->assertSessionHasNoErrors()->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Changed Title', 'language' => 'es', 'genre' => 'fantasy']);

        $this->actingAs($user)->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('r2')->assertMissing($coverPath);
    }

    public function test_deleting_an_author_deletes_their_book_cover_objects(): void
    {
        Storage::fake('r2');
        $user = $this->author();
        $book = $this->bookFor($user);
        $coverPath = 'book-covers/user-deleted-cover.jpg';
        Storage::disk('r2')->put($coverPath, 'cover');
        $book->update(['cover_image_path' => $coverPath]);

        app(DeleteUser::class)->handle($user);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        Storage::disk('r2')->assertMissing($coverPath);
    }

    public function test_author_cannot_edit_update_or_delete_another_authors_book(): void
    {
        $owner = $this->author();
        $other = $this->author('other-author');
        $book = $this->bookFor($owner);

        $this->actingAs($other)->get(route('books.edit', $book))->assertForbidden();
        $this->actingAs($other)->patch(route('books.update', $book), ['title' => 'Stolen'])->assertForbidden();
        $this->actingAs($other)->delete(route('books.destroy', $book))->assertForbidden();

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'A Book']);
    }

    public function test_update_authorization_runs_before_validation(): void
    {
        $owner = $this->author();
        $other = $this->author('other-author');
        $book = $this->bookFor($owner);

        $this->actingAs($other)->patch(route('books.update', $book), [
            'title' => '',
            'description' => str_repeat('a', 2001),
        ])->assertForbidden()->assertSessionHasNoErrors();
    }

    public function test_paginated_book_order_uses_the_id_to_break_timestamp_ties(): void
    {
        $user = $this->author();
        $createdAt = now();
        $books = collect(range(1, 13))->map(fn (int $number): Book => $user->authorProfile->books()->create([
            'title' => "Book {$number}",
            'language' => 'en',
            'genre' => 'other',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]));

        $this->actingAs($user)->get(route('books.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('books.data.0.id', $books->last()->id)
                ->where('books.data.11.id', $books->get(1)->id));

        $this->actingAs($user)->get(route('books.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('books.data', 1)
                ->where('books.data.0.id', $books->first()->id));
    }

    public function test_public_author_profile_shows_books_newest_first_without_management_data(): void
    {
        $user = $this->author();
        $older = $this->bookFor($user, 'Older');
        $newer = $this->bookFor($user, 'Newer', "A public\ndescription.");
        $older->update(['created_at' => now()->subDay()]);

        $this->get(route('authors.show', $user->authorProfile))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('author-profiles/Show')
                ->has('books.data', 2)
                ->where('books.data.0.id', $newer->id)
                ->where('books.data.0.description', "A public\ndescription.")
                ->where('books.data.0.language', 'en')
                ->where('books.data.1.id', $older->id)
                ->where('books.per_page', 12)
                ->missing('books.data.0.author_profile_id'));
    }

    public function test_deleting_an_author_profile_cascades_to_its_books(): void
    {
        $user = $this->author();
        $book = $this->bookFor($user);

        $user->authorProfile->delete();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    private function author(string $handle = 'an-author'): User
    {
        $user = User::factory()->create();
        $user->authorProfile()->create([
            'display_name' => 'An Author',
            'handle' => $handle,
        ]);

        return $user->refresh();
    }

    private function bookFor(User $user, string $title = 'A Book', ?string $description = null): Book
    {
        if ($user->authorProfile === null) {
            $user = $this->author();
        }

        return $user->authorProfile->books()->create([
            'title' => $title,
            'description' => $description,
            'language' => 'en',
            'genre' => 'other',
        ]);
    }
}
