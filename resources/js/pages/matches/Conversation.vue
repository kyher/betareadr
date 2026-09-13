<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/composables/useTranslation';

type Message = {
    id: number;
    sender_id: number;
    body: string;
    created_at: string;
};
type Page = { messages: Message[]; next_cursor: number | null };
const props = defineProps<{
    match: {
        id: number;
        ended_at: string | null;
        profile: { display_name: string };
    };
    userId: number;
}>();
const { t } = useTranslation();
const messages = ref<Message[]>([]);
const nextCursor = ref<number | null>(null);
const body = ref('');
const error = ref('');
const sending = ref(false);
const ended = ref(Boolean(props.match.ended_at));
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
            }

            throw new Error(
                response.status === 409
                    ? t('This Match has ended.')
                    : t('Could not send message.'),
            );
        }

        body.value = '';
        await catchUp();
    } catch (exception) {
        error.value = String(exception);
    } finally {
        sending.value = false;
    }
}

onMounted(() => {
    void catchUp();

    if (!import.meta.env.VITE_REVERB_APP_KEY) {
        return;
    }

    echo = new Echo({
        broadcaster: 'reverb',
        Pusher,
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
    });
    echo.private(`matches.${props.match.id}`).listen(
        'ConversationMessageSent',
        () => {
            void catchUp();
        },
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
            </div>
        </div>
        <p v-if="ended" class="text-sm text-muted-foreground">
            {{ t('This Match has ended. The Conversation is read-only.') }}
        </p>
        <form v-else class="flex flex-col gap-2" @submit.prevent="send">
            <label for="message">{{ t('Message') }}</label>
            <textarea
                id="message"
                v-model="body"
                maxlength="5000"
                required
                class="min-h-24 rounded-md border bg-background p-2"
            />
            <Button type="submit" :disabled="sending || !body.trim()">{{
                t('Send')
            }}</Button>
        </form>
    </div>
</template>
