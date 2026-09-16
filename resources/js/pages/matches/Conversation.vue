<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import type Echo from 'laravel-echo';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/composables/useTranslation';
import { createConversationEcho } from '@/lib/conversationEcho';

type Message = {
    id: number;
    sender_id: number | null;
    body: string;
    created_at: string;
};
type Page = { messages: Message[]; next_cursor: number | null };
const props = defineProps<{
    match: {
        id: number;
        ended_at: string | null;
        ended_by: 'author' | 'beta_reader' | null;
        profile: { display_name: string };
        book: { title: string };
    };
    userId: number;
}>();
const { t } = useTranslation();
const messages = ref<Message[]>([]);
const nextCursor = ref<number | null>(null);
const body = ref('');
const error = ref('');
const sending = ref(false);
const sendState = ref<'idle' | 'sending' | 'sent' | 'failed'>('idle');
const ended = ref(Boolean(props.match.ended_at));
watch(
    () => props.match.ended_at,
    (value) => {
        ended.value = Boolean(value);
    },
);
let echo: Echo<'reverb'> | undefined;

const url = `/matches/${props.match.id}/messages`;
async function page(cursor?: number): Promise<Page> {
    const response = await fetch(cursor ? `${url}?cursor=${cursor}` : url, {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error(t('Could not load messages.'));
    }

    return response.json();
}

async function loadOlder() {
    try {
        const result = await page(nextCursor.value ?? undefined);
        const seen = new Set(messages.value.map((message) => message.id));
        messages.value.push(
            ...result.messages.filter((message) => !seen.has(message.id)),
        );
        nextCursor.value = result.next_cursor;
        error.value = '';
    } catch (exception) {
        error.value = String(exception);
    }
}

async function catchUp() {
    try {
        const knownId = messages.value[0]?.id ?? 0;
        let cursor: number | null = null;
        const incoming: Message[] = [];

        do {
            const result = await page(cursor ?? undefined);
            incoming.push(
                ...result.messages.filter((message) => message.id > knownId),
            );
            cursor = result.next_cursor;

            if (!knownId) {
                nextCursor.value = cursor;
            }
        } while (
            knownId &&
            cursor &&
            incoming.length &&
            incoming.at(-1)!.id > knownId
        );

        const seen = new Set(messages.value.map((message) => message.id));
        messages.value.unshift(
            ...incoming.filter((message) => !seen.has(message.id)),
        );
        error.value = '';
    } catch (exception) {
        error.value = String(exception);
    }
}

async function send() {
    if (!body.value.trim() || sending.value) {
        return;
    }

    sending.value = true;
    sendState.value = 'sending';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>(
                    'meta[name="csrf-token"]',
                )!.content,
            },
            body: JSON.stringify({ body: body.value }),
        });

        if (!response.ok) {
            if (response.status === 409) {
                ended.value = true;
                router.reload({ only: ['match'] });
            }

            throw new Error(
                response.status === 409
                    ? t('This Match has ended.')
                    : t('Could not send message.'),
            );
        }

        const { message }: { message: Message } = await response.json();

        if (!messages.value.some((existing) => existing.id === message.id)) {
            messages.value.unshift(message);
        }

        body.value = '';
        error.value = '';
        sendState.value = 'sent';
    } catch (exception) {
        error.value = String(exception);
        sendState.value = 'failed';
    } finally {
        sending.value = false;
    }
}

function onKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
        event.preventDefault();
        void send();
    }
}

onMounted(() => {
    void catchUp();

    echo = createConversationEcho();

    if (!echo) {
        return;
    }

    echo.private(`matches.${props.match.id}`).listen(
        'ConversationMessageSent',
        () => {
            void catchUp();
        },
    );
    echo.private(`matches.${props.match.id}`).listen(
        'BetaReadingMatchEnded',
        () => router.reload({ only: ['match'] }),
    );
    echo.connector.pusher.connection.bind('connected', () => {
        void catchUp();
    });
});

onUnmounted(() => {
    echo?.disconnect();
});

defineOptions({
    layout: { breadcrumbs: [{ title: 'Matches', href: '/matches' }] },
});
</script>

<template>
    <Head :title="t('Conversation')" />
    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-4 md:p-8">
        <Link href="/matches" class="text-sm underline">{{
            t('Matches')
        }}</Link>
        <h1 class="text-2xl font-semibold">
            {{ t('Conversation') }} · {{ match.profile.display_name }}
        </h1>
        <p class="text-sm text-muted-foreground">{{ match.book.title }}</p>
        <p v-if="error" role="alert" class="text-sm text-destructive">
            {{ error }}
        </p>
        <Button
            v-if="nextCursor"
            type="button"
            variant="outline"
            @click="loadOlder"
            >{{ t('Load older messages') }}</Button
        >
        <div class="flex flex-col gap-3" aria-live="polite">
            <div
                v-for="message in [...messages].reverse()"
                :key="message.id"
                class="rounded-lg border p-3"
            >
                <p class="text-xs text-muted-foreground">
                    {{
                        message.sender_id === userId
                            ? t('You')
                            : match.profile.display_name
                    }}
                </p>
                <p class="break-words whitespace-pre-wrap">
                    {{ message.body }}
                </p>
                <time
                    class="text-xs text-muted-foreground"
                    :datetime="message.created_at"
                    >{{ new Date(message.created_at).toLocaleString() }}</time
                >
            </div>
        </div>
        <p v-if="ended" class="text-sm text-muted-foreground">
            {{ t('This Match has ended. The Conversation is read-only.') }}
            {{ t('Ended by') }}
            <span class="lowercase">{{
                match.ended_by === 'author'
                    ? t('Author')
                    : match.ended_by === 'beta_reader'
                      ? t('Beta Reader')
                      : t('participant')
            }}</span>
            ·
            {{
                match.ended_at ? new Date(match.ended_at).toLocaleString() : ''
            }}
        </p>
        <form v-else class="flex flex-col gap-2" @submit.prevent="send">
            <label for="message">{{ t('Message') }}</label>
            <textarea
                id="message"
                v-model="body"
                maxlength="5000"
                required
                class="min-h-24 rounded-md border bg-background p-2"
                @keydown="onKeydown"
            />
            <p v-if="sendState !== 'idle'" role="status" class="text-sm">
                {{
                    t(
                        sendState === 'sending'
                            ? 'Sending'
                            : sendState === 'sent'
                              ? 'Sent'
                              : 'Failed to send',
                    )
                }}
            </p>
            <Button type="submit" :disabled="sending || !body.trim()">{{
                t(sendState === 'failed' ? 'Retry send' : 'Send')
            }}</Button>
        </form>
    </div>
</template>
