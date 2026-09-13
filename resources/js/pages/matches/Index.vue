<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Handshake } from '@lucide/vue';
import MatchController from '@/actions/App/Http/Controllers/MatchController';
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

type Match = {
    id: number;
    role: 'author' | 'beta_reader';
    profile: { display_name: string; handle: string };
    book: { id: number; title: string };
    listing: { id: number; reading_brief: string };
    created_at: string;
    ended_at: string | null;
    unread_count: number;
};

defineProps<{ matches: Match[] }>();

const { t } = useTranslation();

defineOptions({
    layout: { breadcrumbs: [{ title: 'Matches', href: '/matches' }] },
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
                    <div class="min-w-0 flex-1">
                        <CardTitle>
                            <Link
                                class="hover:underline"
                                :href="
                                    match.role === 'author'
                                        ? `/beta-readers/${match.profile.handle}`
                                        : `/authors/${match.profile.handle}`
                                "
                            >
                                {{ match.profile.display_name }}
                            </Link>
                        </CardTitle>
                        <CardDescription class="mt-1">
                            {{ match.book.title }} ·
                            {{ match.ended_at ? t('Ended') : t('Active') }}
                        </CardDescription>
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
                            <Link :href="`/matches/${match.id}/conversation`">
                                {{ t('Conversation')
                                }}<span v-if="match.unread_count">
                                    ({{ match.unread_count }})</span
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
