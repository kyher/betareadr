<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Handshake } from '@lucide/vue';
import type Echo from 'laravel-echo';
import { onMounted, onUnmounted } from 'vue';
import MatchController from '@/actions/App/Http/Controllers/MatchController';
import BookMetadata from '@/components/BookMetadata.vue';
import ContentWarnings from '@/components/ContentWarnings.vue';
import ProfileAvatar from '@/components/ProfileAvatar.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useTranslation } from '@/composables/useTranslation';
import { createConversationEcho } from '@/lib/conversationEcho';
import { refreshUnreadMessages as refresh } from '@/lib/refreshUnreadMessages';
import { show as showAuthor } from '@/routes/authors';
import { show as showBetaReader } from '@/routes/beta-readers';
import { conversation, index as matchesIndex } from '@/routes/matches';
import type { Book } from '@/types';

type Match = {
    id: number;
    role: 'author' | 'beta_reader';
    profile: {
        display_name: string;
        handle: string;
        avatar_url: string | null;
    };
    book: Pick<
        Book,
        'id' | 'title' | 'language' | 'genre' | 'content_warnings'
    >;
    listing: { id: number; reading_brief: string };
    created_at: string;
    ended_at: string | null;
    ended_by: 'author' | 'beta_reader' | null;
    unread_count: number;
    latest_message: { body: string; created_at: string } | null;
};

const props = defineProps<{ matches: Match[] }>();
let echo: Echo<'reverb'> | undefined;

onMounted(() => {
    if (!props.matches.length) {
        return;
    }

    echo = createConversationEcho();

    if (!echo) {
        return;
    }

    for (const match of props.matches) {
        echo.private(`matches.${match.id}`)
            .listen('ConversationMessageSent', refresh)
            .listen('BetaReadingMatchEnded', refresh);
    }

    echo.connector.pusher.connection.bind('connected', refresh);
});
onUnmounted(() => echo?.disconnect());

const { t } = useTranslation();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Matches', href: matchesIndex() }] },
});
</script>

<template>
    <Head :title="t('Matches')" />
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ t('Matches') }}
            </h1>
            <p class="mt-1 text-muted-foreground">
                {{ t('Your approved beta reading connections.') }}
            </p>
        </div>

        <Card v-if="matches.length === 0" class="border-dashed">
            <CardContent
                class="flex flex-col items-center gap-3 py-10 text-center text-muted-foreground"
            >
                <Handshake class="size-8" />
                {{ t('You do not have any Matches yet.') }}
            </CardContent>
        </Card>

        <div v-else class="grid gap-4">
            <Card v-for="match in matches" :key="match.id">
                <CardHeader class="gap-4 sm:flex-row sm:items-start">
                    <ProfileAvatar :profile="match.profile" class="size-12" />
                    <div class="min-w-0 flex-1">
                        <CardTitle>
                            <Link
                                class="hover:underline"
                                :href="
                                    match.role === 'author'
                                        ? showBetaReader(match.profile.handle)
                                        : showAuthor(match.profile.handle)
                                "
                            >
                                {{ match.profile.display_name }}
                            </Link>
                        </CardTitle>
                        <CardDescription class="mt-1">
                            {{ match.book.title }} ·
                            {{ match.ended_at ? t('Ended') : t('Active') }}
                        </CardDescription>
                        <BookMetadata
                            :language="match.book.language"
                            :genre="match.book.genre"
                        />
                        <ContentWarnings
                            :warnings="match.book.content_warnings"
                        />
                        <p
                            v-if="match.ended_at"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{ t('Ended by') }}
                            <span class="lowercase">{{
                                match.ended_by === 'author'
                                    ? t('Author')
                                    : match.ended_by === 'beta_reader'
                                      ? t('Beta Reader')
                                      : t('participant')
                            }}</span>
                            · {{ new Date(match.ended_at).toLocaleString() }}
                        </p>
                        <p
                            v-if="match.latest_message"
                            class="mt-2 truncate text-sm text-muted-foreground"
                        >
                            {{ match.latest_message.body }} ·
                            {{
                                new Date(
                                    match.latest_message.created_at,
                                ).toLocaleString()
                            }}
                        </p>
                        <p v-else class="mt-2 text-sm text-muted-foreground">
                            {{ t('No messages yet') }}
                        </p>
                        <p class="mt-2 text-sm font-medium text-foreground">
                            {{
                                match.role === 'author'
                                    ? t('You’re the Author')
                                    : t('You’re the Beta Reader')
                            }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Button size="sm" as-child>
                            <Link :href="conversation(match.id)">
                                {{ t('Conversation') }}
                                <span
                                    v-if="match.unread_count"
                                    :aria-label="`${match.unread_count} ${t('unread')}`"
                                    >({{ match.unread_count }})</span
                                >
                            </Link>
                        </Button>
                        <Dialog v-if="!match.ended_at">
                            <DialogTrigger as-child>
                                <Button variant="destructive" size="sm">
                                    {{ t('End match') }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="MatchController.end.form(match.id)"
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle>{{
                                            t('End this match?')
                                        }}</DialogTitle>
                                        <DialogDescription>
                                            {{
                                                t(
                                                    'This is permanent. The original interest cannot be reconsidered.',
                                                )
                                            }}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-6 gap-2">
                                        <DialogClose as-child>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                            >
                                                {{ t('Cancel') }}
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            {{ t('End match') }}
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardHeader>
                <CardContent>
                    <p class="text-sm font-medium">{{ t('Reading Brief') }}</p>
                    <p
                        class="reading-copy mt-1 line-clamp-3 text-sm whitespace-pre-line text-muted-foreground"
                    >
                        {{ match.listing.reading_brief }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
