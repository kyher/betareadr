<?php

namespace Tests\Feature;

use App\Actions\BetaReadingListings\EndBetaReadingMatch;
use App\Events\ConversationMessageSent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('broadcasting.default', 'reverb');
        config()->set('broadcasting.connections.reverb.key', 'test-key');
        config()->set('broadcasting.connections.reverb.secret', 'test-secret');
        config()->set('broadcasting.connections.reverb.app_id', 'test-id');
        require base_path('routes/channels.php');
    }

    public function test_only_participants_can_open_list_send_and_subscribe(): void
    {
        [$match, $author, $reader] = $this->match();
        $outsider = User::factory()->create();

        $this->get(route('matches.conversation', $match))->assertRedirect(route('login'));
        $this->actingAs($outsider)->get(route('matches.conversation', $match))->assertForbidden();
        $this->actingAs($outsider)->getJson(route('matches.messages.index', $match))->assertForbidden();
        $this->actingAs($outsider)->postJson(route('matches.messages.store', $match), ['body' => 'No'])->assertForbidden();
        $this->actingAs($outsider)->postJson('/broadcasting/auth', ['channel_name' => 'private-matches.'.$match->id, 'socket_id' => '1.2'])->assertForbidden();

        $this->actingAs($author)->get(route('matches.conversation', $match))
            ->assertInertia(fn (Assert $page) => $page->component('matches/Conversation')->where('match.id', $match->id));
        $this->actingAs($reader)->get(route('matches.conversation', $match))->assertOk();
        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-matches.'.$match->id, 'socket_id' => '1.2'])->assertOk();
    }

    public function test_messages_are_plain_text_validated_and_read_only_after_ending(): void
    {
        [$match, $author, $reader] = $this->match();
        $send = route('matches.messages.store', $match);

        $this->actingAs($author)->postJson($send, ['body' => '  '])->assertUnprocessable();
        $this->postJson($send, ['body' => str_repeat('😀', 5001)])->assertUnprocessable();
        $this->postJson($send, ['body' => ''])->assertUnprocessable();
        $this->postJson($send, ['body' => str_repeat('😀', 5000)])->assertCreated();
        $this->actingAs($reader)->postJson($send, ['body' => '<script>alert(1)</script>'])->assertCreated();
        $this->assertDatabaseHas('conversation_messages', ['beta_reading_match_id' => $match->id, 'sender_id' => $reader->id, 'body' => '<script>alert(1)</script>']);
        $this->assertNotNull($match->messages()->latest('id')->first()->created_at);

        $notification = new ConversationMessageSent($match->id, $match->messages()->max('id'));
        $this->assertSame(['match_id' => $match->id, 'message_id' => $match->messages()->max('id')], $notification->broadcastWith());

        app(EndBetaReadingMatch::class)->handle($match);
        $this->postJson($send, ['body' => 'Too late'])->assertStatus(409);
        $this->getJson(route('matches.messages.index', $match))->assertOk()->assertJsonCount(2, 'messages');
    }

    public function test_cursor_pages_are_newest_first_and_opening_marks_only_received_messages_read(): void
    {
        [$match, $author, $reader] = $this->match();
        for ($i = 1; $i <= 52; $i++) {
            $match->messages()->create(['sender_id' => $reader->id, 'body' => "Message {$i}", 'created_at' => now()]);
        }
        $match->messages()->create(['sender_id' => $author->id, 'body' => 'Mine', 'created_at' => now()]);

        $this->actingAs($author)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 52));
        $this->get(route('matches.conversation', $match))->assertOk();
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 0));
        $first = $this->getJson(route('matches.messages.index', $match))->assertOk()->assertJsonCount(50, 'messages')->json();
        $this->assertSame('Mine', $first['messages'][0]['body']);
        $this->assertSame(4, $first['next_cursor']);
        $match->messages()->create(['sender_id' => $reader->id, 'body' => 'New while paging']);
        $second = $this->getJson(route('matches.messages.index', ['match' => $match, 'cursor' => $first['next_cursor']]))->assertOk()->assertJsonCount(3, 'messages')->json();
        $this->assertNull($second['next_cursor']);
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 1));
        $this->getJson(route('matches.messages.index', $match))->assertOk();
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 0));
        $this->actingAs($reader)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 1));
    }

    public function test_persistence_survives_broadcast_failure_and_deletion_cascades(): void
    {
        [$match, $author] = $this->match();
        Event::listen(ConversationMessageSent::class, fn () => throw new RuntimeException('Reverb unavailable'));

        $this->actingAs($author)->postJson(route('matches.messages.store', $match), ['body' => 'Durable'])->assertCreated();
        $this->assertDatabaseHas('conversation_messages', ['beta_reading_match_id' => $match->id, 'body' => 'Durable']);
        $this->getJson(route('matches.messages.index', $match))->assertOk()->assertJsonPath('messages.0.body', 'Durable');
        $match->readerDecision->betaReadingListing->book->delete();
        $this->assertDatabaseMissing('conversation_messages', ['beta_reading_match_id' => $match->id]);
    }

    public function test_deleting_a_user_removes_ordinary_conversation_history(): void
    {
        [$match, $author, $reader] = $this->match();
        $this->actingAs($reader)->postJson(route('matches.messages.store', $match), ['body' => 'History'])->assertCreated();

        $author->delete();

        $this->assertDatabaseMissing('conversation_messages', ['beta_reading_match_id' => $match->id]);
    }

    private function match(): array
    {
        $author = User::factory()->create();
        $authorProfile = $author->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author']);
        $book = $authorProfile->books()->create(['title' => 'Book', 'language' => 'en']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Draft', 'word_count' => 100]);
        $reader = User::factory()->create();
        $profile = $reader->betaReaderProfile()->create(['display_name' => 'Reader', 'handle' => 'reader', 'reading_languages' => ['*']]);
        $decision = $profile->readerDecisions()->create(['beta_reading_listing_id' => $listing->id, 'reader_decision' => 'interested', 'author_decision' => 'approved']);

        return [$decision->match()->create([]), $author, $reader];
    }
}
