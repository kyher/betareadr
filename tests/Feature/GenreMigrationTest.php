<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GenreMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_migration_backfills_and_requires_a_genre(): void
    {
        $migration = require database_path('migrations/2026_09_25_000000_require_genre_on_books.php');
        $migration->down();
        $author = User::factory()->create()->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author']);
        $bookId = DB::table('books')->insertGetId([
            'author_profile_id' => $author->id, 'title' => 'Book', 'language' => 'en', 'content_warnings' => '[]',
        ]);

        $migration->up();

        $this->assertDatabaseHas('books', ['id' => $bookId, 'genre' => 'other']);
        $this->expectException(QueryException::class);
        DB::table('books')->insert([
            'author_profile_id' => $author->id, 'title' => 'Another Book', 'language' => 'en', 'content_warnings' => '[]',
        ]);
    }
}
