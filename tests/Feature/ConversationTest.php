<?php

namespace Tests\Feature;

use App\Actions\BetaReadingListings\EndBetaReadingMatch;
use App\Actions\Conversations\MarkConversationRead;
use App\Events\BetaReadingMatchEnded;
use App\Events\ConversationMessageSent;
use App\Events\UnreadMessagesChanged;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        $this->postJson(route('matches.read', $match), ['message_id' => 1])->assertForbidden();
        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-users.'.$author->id, 'socket_id' => '1.2'])->assertForbidden();
        $this->actingAs($outsider)->postJson('/broadcasting/auth', ['channel_name' => 'private-matches.'.$match->id, 'socket_id' => '1.2'])->assertForbidden();

        $this->actingAs($author)->get(route('matches.conversation', $match))
            ->assertInertia(fn (Assert $page) => $page
                ->component('matches/Conversation')
                ->where('match.id', $match->id)
                ->where('match.book.language', 'en')
                ->where('match.book.genre', 'fantasy'));
        $this->actingAs($reader)->get(route('matches.conversation', $match))->assertOk();
        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-matches.'.$match->id, 'socket_id' => '1.2'])->assertOk();
        $this->postJson('/broadcasting/auth', ['channel_name' => 'private-users.'.$reader->id, 'socket_id' => '1.2'])->assertOk();
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

        $notification = new ConversationMessageSent($match->id, $match->messages()->max('id'), $author->id);
        $this->assertSame(['match_id' => $match->id, 'message_id' => $match->messages()->max('id')], $notification->broadcastWith());
        $this->assertSame(['private-matches.'.$match->id, 'private-users.'.$author->id], array_map(fn ($channel) => (string) $channel, $notification->broadcastOn()));

        app(EndBetaReadingMatch::class)->handle($match, $author);
        $this->postJson($send, ['body' => 'Too late'])->assertStatus(409);
        $this->getJson(route('matches.messages.index', $match))->assertOk()->assertJsonCount(2, 'messages');
    }

    public function test_fetching_does_not_mark_read_and_explicit_read_clears_only_received_messages_through_its_cutoff(): void
    {
        [$match, $author, $reader] = $this->match();
        for ($i = 1; $i <= 52; $i++) {
            $message = $match->messages()->create(['sender_id' => $reader->id, 'body' => "Message {$i}", 'created_at' => now()]);
            if ($i === 4) {
                $fourthMessageId = $message->id;
            }
        }
        $match->messages()->create(['sender_id' => $author->id, 'body' => 'Mine', 'created_at' => now()]);

        $this->actingAs($author)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 52));
        $this->get(route('matches.conversation', $match))->assertOk();
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 52));
        $first = $this->getJson(route('matches.messages.index', $match))->assertOk()->assertJsonCount(50, 'messages')->json();
        $this->assertSame('Mine', $first['messages'][0]['body']);
        $this->assertSame($fourthMessageId, $first['next_cursor']);
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('unreadMessageCount', 52));
        $this->postJson(route('matches.read', $match), ['message_id' => $first['messages'][0]['id']])->assertOk()->assertJsonPath('unreadMessageCount', 0);
        $match->messages()->create(['sender_id' => $reader->id, 'body' => 'New while paging']);
        $second = $this->getJson(route('matches.messages.index', ['match' => $match, 'cursor' => $first['next_cursor']]))->assertOk()->assertJsonCount(3, 'messages')->json();
        $this->assertNull($second['next_cursor']);
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 1));
        $this->getJson(route('matches.messages.index', $match))->assertOk();
        $this->postJson(route('matches.read', $match), ['message_id' => $first['messages'][0]['id']])->assertOk()->assertJsonPath('unreadMessageCount', 1);
        $this->postJson(route('matches.read', $match), ['message_id' => $match->messages()->max('id')])->assertOk()->assertJsonPath('unreadMessageCount', 0);
        $this->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 0));
        $this->actingAs($reader)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page->where('matches.0.unread_count', 1));
    }

    public function test_global_unread_counts_combine_both_profiles_and_ended_matches_and_notify_only_the_affected_user(): void
    {
        Event::fake([UnreadMessagesChanged::class, ConversationMessageSent::class, BetaReadingMatchEnded::class]);
        [$authorMatch, $user, $reader] = $this->match();
        [$readerMatch, $otherAuthor] = $this->match('-two');
        [$unrelatedMatch, $outsider] = $this->match('-unrelated');
        $profile = $user->betaReaderProfile()->create(['display_name' => 'Both', 'handle' => 'both', 'reading_languages' => ['*']]);
        $readerMatch->readerDecision->forceFill(['beta_reader_profile_id' => $profile->id])->save();

        $this->actingAs($reader)->postJson(route('matches.messages.store', $authorMatch), ['body' => 'One'])->assertCreated();
        $this->actingAs($otherAuthor)->postJson(route('matches.messages.store', $readerMatch), ['body' => 'Two'])->assertCreated();
        Event::assertDispatchedTimes(ConversationMessageSent::class, 2);
        Event::assertDispatched(ConversationMessageSent::class, fn ($event) => $event->matchId === $authorMatch->id && $event->recipientUserId === $user->id);
        Event::assertDispatched(ConversationMessageSent::class, fn ($event) => $event->matchId === $readerMatch->id && $event->recipientUserId === $user->id);
        Event::assertNotDispatched(UnreadMessagesChanged::class);
        $this->actingAs($user)->postJson(route('matches.messages.store', $readerMatch), ['body' => 'Mine'])->assertCreated();
        Event::assertDispatched(ConversationMessageSent::class, fn ($event) => $event->matchId === $readerMatch->id && $event->recipientUserId === $otherAuthor->id);
        Event::assertNotDispatched(UnreadMessagesChanged::class);
        $unrelatedMatch->messages()->create(['sender_id' => $outsider->id, 'body' => 'Unrelated']);
        app(EndBetaReadingMatch::class)->handle($authorMatch, $reader);
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('unreadMessageCount', 2));

        $this->postJson(route('matches.read', $authorMatch), ['message_id' => $readerMatch->messages()->max('id')])->assertUnprocessable();
        $this->postJson(route('matches.read', $authorMatch), [])->assertUnprocessable();
        Event::fake([UnreadMessagesChanged::class]);
        $this->postJson(route('matches.read', $authorMatch), ['message_id' => $authorMatch->messages()->max('id')])->assertOk()->assertJsonPath('unreadMessageCount', 1);
        Event::assertDispatchedTimes(UnreadMessagesChanged::class, 1);
        Event::assertDispatched(UnreadMessagesChanged::class, fn ($event) => $event->userId === $user->id);
        $this->postJson(route('matches.read', $authorMatch), ['message_id' => $authorMatch->messages()->max('id')])->assertOk();
        Event::assertDispatchedTimes(UnreadMessagesChanged::class, 1);
        $this->assertSame('private-users.'.$user->id, (string) (new UnreadMessagesChanged($user->id))->broadcastOn()[0]);
        $this->assertSame([], (new UnreadMessagesChanged($user->id))->broadcastWith());

        $readerMatch->readerDecision->betaReadingListing->book->delete();
        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('unreadMessageCount', 0));
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

    public function test_account_deletion_removes_matches_and_all_messages_for_both_roles(): void
    {
        foreach (['author', 'reader'] as $role) {
            [$match, $author, $reader] = $this->match('-'.$role);
            $deleted = $role === 'author' ? $author : $reader;
            $remaining = $role === 'author' ? $reader : $author;
            $decision = $match->readerDecision;
            $book = $decision->betaReadingListing->book;
            $match->messages()->create(['sender_id' => $deleted->id, 'body' => 'From deleted participant']);
            $match->messages()->create(['sender_id' => $remaining->id, 'body' => 'From remaining participant']);

            $this->actingAs($deleted)->delete(route('profile.destroy'), ['password' => 'password'])
                ->assertSessionHasNoErrors()->assertRedirect(route('home'));
            $this->assertGuest();
            $this->assertNull($deleted->fresh());
            $this->assertNull($match->fresh());
            $this->assertNull($decision->fresh());
            $this->assertDatabaseMissing('conversation_messages', ['beta_reading_match_id' => $match->id]);
            $this->assertDatabaseMissing($role === 'author' ? 'author_profiles' : 'beta_reader_profiles', ['user_id' => $deleted->id]);
            $this->assertSame($role === 'reader', $book->fresh() !== null);
            $this->assertNotNull($remaining->fresh());

            $this->actingAs($remaining)->get(route('matches.index'))
                ->assertInertia(fn (Assert $page) => $page->has('matches', 0));
            $this->get(route('matches.conversation', $match))->assertNotFound();
            $this->getJson(route('matches.messages.index', $match))->assertNotFound();
            $this->postJson(route('matches.messages.store', $match), ['body' => 'Too late'])->assertNotFound();
            $this->postJson('/broadcasting/auth', ['channel_name' => 'private-matches.'.$match->id, 'socket_id' => '1.2'])->assertForbidden();
        }
    }

    public function test_deletion_removes_ended_matches_and_both_profile_roles_without_other_users_data(): void
    {
        [$authorMatch, $user, $reader] = $this->match();
        [$readerMatch, $otherAuthor] = $this->match('-other');
        [$unrelatedMatch, $unrelatedAuthor] = $this->match('-unrelated');
        $profile = $user->betaReaderProfile()->create(['display_name' => 'Both roles', 'handle' => 'both', 'reading_languages' => ['*']]);
        $readerMatch->readerDecision->forceFill(['beta_reader_profile_id' => $profile->id])->save();
        $unrelatedMatch->readerDecision->forceFill(['beta_reader_profile_id' => $reader->betaReaderProfile->id])->save();
        app(EndBetaReadingMatch::class)->handle($authorMatch, $reader);
        foreach ([$authorMatch, $readerMatch] as $match) {
            $match->messages()->create(['sender_id' => $user->id, 'body' => 'Deleted']);
        }
        $unrelatedMatch->messages()->create(['sender_id' => $reader->id, 'body' => 'Kept']);
        $otherListing = $readerMatch->readerDecision->betaReadingListing;
        $pass = $profile->readerDecisions()->create(['beta_reading_listing_id' => $unrelatedMatch->readerDecision->beta_reading_listing_id, 'reader_decision' => 'passed']);
        $ownedProfile = $user->authorProfile;
        $ownedBook = $authorMatch->readerDecision->betaReadingListing->book;
        $ownedListing = $authorMatch->readerDecision->betaReadingListing;

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect(route('home'));

        foreach ([$user, $profile, $ownedProfile, $ownedBook, $ownedListing, $pass, $authorMatch, $readerMatch] as $record) {
            $this->assertNull($record->fresh());
        }
        $this->assertDatabaseCount('conversation_messages', 1);
        $this->assertDatabaseHas('conversation_messages', ['beta_reading_match_id' => $unrelatedMatch->id, 'body' => 'Kept']);
        foreach ([$reader, $otherAuthor, $unrelatedAuthor, $otherListing, $otherListing->book, $unrelatedMatch] as $record) {
            $this->assertNotNull($record->fresh());
        }
        $this->actingAs($reader)->get(route('matches.index'))
            ->assertInertia(fn (Assert $page) => $page->has('matches', 1)->where('matches.0.id', $unrelatedMatch->id));
        $this->actingAs($otherAuthor)->get(route('matches.conversation', $readerMatch))->assertNotFound();
    }

    public function test_match_end_notification_waits_for_the_outer_transaction(): void
    {
        config()->set('broadcasting.default', 'null');
        [$match, $author] = $this->match();
        $notified = false;
        Event::listen(BetaReadingMatchEnded::class, function () use (&$notified): void {
            $notified = true;
        });

        DB::transaction(function () use ($match, $author, &$notified): void {
            app(EndBetaReadingMatch::class)->handle($match, $author);
            $this->assertFalse($notified);
        });

        $this->assertTrue($notified);
    }

    public function test_read_notification_waits_for_the_outer_transaction(): void
    {
        config()->set('broadcasting.default', 'null');
        [$match, $author, $reader] = $this->match();
        $message = $match->messages()->create(['sender_id' => $reader->id, 'body' => 'Read me']);
        $notified = false;
        Event::listen(UnreadMessagesChanged::class, function () use (&$notified): void {
            $notified = true;
        });

        DB::transaction(function () use ($match, $author, $message, &$notified): void {
            app(MarkConversationRead::class)->handle($match, $author, $message->id);
            $this->assertFalse($notified);
        });

        $this->assertTrue($notified);
        $this->assertSame(0, $author->unreadMessageCount());
    }

    public function test_one_inbox_shows_both_roles_with_latest_preview_and_ended_by(): void
    {
        [$authorMatch, $user, $reader] = $this->match();
        [$readerMatch, $otherAuthor] = $this->match('-two');
        $readerProfile = $user->betaReaderProfile()->create(['display_name' => 'Reader side', 'handle' => 'reader-side', 'reading_languages' => ['*']]);
        $readerMatch->readerDecision->forceFill(['beta_reader_profile_id' => $readerProfile->id])->save();

        $this->actingAs($reader)->postJson(route('matches.messages.store', $authorMatch), ['body' => 'Latest note'])->assertCreated();
        $this->actingAs($otherAuthor)->delete(route('matches.end', $readerMatch))->assertRedirect();

        $this->actingAs($user)->get(route('matches.index'))->assertInertia(fn (Assert $page) => $page
            ->component('matches/Index')
            ->has('matches', 2)
            ->where('matches.0.role', 'beta_reader')
            ->where('matches.0.profile.display_name', 'Author')
            ->where('matches.0.ended_by', 'author')
            ->where('matches.0.unread_count', 0)
            ->where('matches.1.role', 'author')
            ->where('matches.1.profile.display_name', 'Reader')
            ->where('matches.1.latest_message.body', 'Latest note')
            ->where('matches.1.unread_count', 1));
    }

    private function match(string $suffix = ''): array
    {
        $author = User::factory()->create();
        $authorProfile = $author->authorProfile()->create(['display_name' => 'Author', 'handle' => 'author'.$suffix]);
        $book = $authorProfile->books()->create(['title' => 'Book', 'language' => 'en', 'genre' => 'fantasy']);
        $listing = $book->betaReadingListings()->create(['reading_brief' => 'Draft', 'word_count' => 100]);
        $reader = User::factory()->create();
        $profile = $reader->betaReaderProfile()->create(['display_name' => 'Reader', 'handle' => 'reader'.$suffix, 'reading_languages' => ['*']]);
        $decision = $profile->readerDecisions()->create(['beta_reading_listing_id' => $listing->id, 'reader_decision' => 'interested', 'author_decision' => 'approved']);

        return [$decision->match()->create([]), $author, $reader];
    }
}
