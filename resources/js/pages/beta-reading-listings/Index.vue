<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Pencil, Plus, RotateCcw, Users, XCircle } from '@lucide/vue';
import { computed } from 'vue';
import BetaReadingListingController from '@/actions/App/Http/Controllers/BetaReadingListingController';
import BetaReadingListingReviewController from '@/actions/App/Http/Controllers/BetaReadingListingReviewController';
import BookMetadata from '@/components/BookMetadata.vue';
import ContentWarnings from '@/components/ContentWarnings.vue';
import InputError from '@/components/InputError.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Badge } from '@/components/ui/badge';
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
import { index as books } from '@/routes/books';
import type { Book } from '@/types';

type BookSummary = Pick<
    Book,
    'id' | 'title' | 'language' | 'genre' | 'content_warnings'
>;
type Listing = {
    id: number;
    reading_brief: string;
    word_count: number;
    closed_at: string | null;
    created_at: string;
    interested_count: number;
    approved_count: number;
};
type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{
    book: BookSummary;
    hasOpenListing: boolean;
    listings: Paginator<Listing>;
}>();

const page = usePage();
const { t } = useTranslation();
const listingError = computed(() => {
    const errors = page.props.errors as Record<string, string> | undefined;

    return errors?.listing;
});

const formatWords = (wordCount: number): string =>
    new Intl.NumberFormat(page.props.locale).format(wordCount);

const formatDate = (date: string): string =>
    new Intl.DateTimeFormat(page.props.locale, { dateStyle: 'medium' }).format(
        new Date(date),
    );

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Listings', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${book.title} · ${t('Listings')}`" />
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm text-muted-foreground">
                    {{ t('Beta Reading Listings') }}
                </p>
                <h1 class="truncate text-2xl font-semibold tracking-tight">
                    {{ book.title }}
                </h1>
                <BookMetadata :language="book.language" :genre="book.genre" />
                <ContentWarnings :warnings="book.content_warnings" />
                <p class="mt-1 text-muted-foreground">
                    {{ t('Manage recruitment rounds for this book.') }}
                </p>
            </div>
            <div
                v-if="hasOpenListing"
                class="flex max-w-64 flex-col items-end gap-1"
            >
                <Button
                    disabled
                    aria-describedby="create-listing-disabled-reason"
                >
                    <Plus /> {{ t('Create Listing') }}
                </Button>
                <p
                    id="create-listing-disabled-reason"
                    class="text-right text-xs text-muted-foreground"
                >
                    {{
                        t('Close the open Listing before creating another one.')
                    }}
                </p>
            </div>
            <Button v-else as-child>
                <Link :href="BetaReadingListingController.create(book.id)">
                    <Plus /> {{ t('Create Listing') }}
                </Link>
            </Button>
        </div>

        <InputError v-if="listingError" :message="listingError" />

        <Card v-if="listings.data.length === 0" class="border-dashed">
            <CardHeader>
                <div
                    class="mb-3 flex size-12 items-center justify-center rounded-full bg-muted"
                >
                    <BookOpen class="size-6" />
                </div>
                <CardTitle>{{ t('Create the first Listing') }}</CardTitle>
                <CardDescription>
                    {{
                        t(
                            'Describe the current draft and the feedback you want from beta readers.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="BetaReadingListingController.create(book.id)">
                        <Plus /> {{ t('Create Listing') }}
                    </Link>
                </Button>
            </CardContent>
        </Card>

        <div v-else class="grid gap-4">
            <Card
                v-for="listing in listings.data"
                :key="listing.id"
                class="paper-sheet"
            >
                <CardHeader class="gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <CardTitle>
                                {{ formatWords(listing.word_count) }}
                                {{ t('words') }}
                            </CardTitle>
                            <Badge
                                :variant="
                                    listing.closed_at ? 'secondary' : 'default'
                                "
                            >
                                {{ t(listing.closed_at ? 'Closed' : 'Open') }}
                            </Badge>
                        </div>
                        <CardDescription class="mt-1">
                            {{ t('Created') }}
                            {{ formatDate(listing.created_at) }}
                            <template v-if="listing.closed_at">
                                · {{ t('Closed') }}
                                {{ formatDate(listing.closed_at) }}
                            </template>
                        </CardDescription>
                        <p
                            class="reading-copy mt-4 leading-6 whitespace-pre-line"
                        >
                            {{ listing.reading_brief }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:shrink-0">
                        <Button
                            class="h-auto max-w-full whitespace-normal"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link
                                :href="
                                    BetaReadingListingReviewController.index({
                                        book: book.id,
                                        listing: listing.id,
                                    })
                                "
                            >
                                <Users />
                                {{ t('Review beta readers') }}
                                · {{ listing.interested_count }}
                                {{ t('pending') }} ·
                                {{ listing.approved_count }} {{ t('approved') }}
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <Link
                                :href="
                                    BetaReadingListingController.edit({
                                        book: book.id,
                                        listing: listing.id,
                                    })
                                "
                            >
                                <Pencil /> {{ t('Edit') }}
                            </Link>
                        </Button>

                        <Dialog v-if="!listing.closed_at">
                            <DialogTrigger as-child>
                                <Button variant="outline" size="sm">
                                    <XCircle /> {{ t('Close') }}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="
                                        BetaReadingListingController.close.form(
                                            {
                                                book: book.id,
                                                listing: listing.id,
                                            },
                                        )
                                    "
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle>{{
                                            t('Close this Listing?')
                                        }}</DialogTitle>
                                        <DialogDescription>
                                            {{
                                                t(
                                                    'It will leave beta reader feeds and accept no new Interest. Existing records are preserved, and you can reopen it later.',
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
                                            :disabled="processing"
                                        >
                                            {{ t('Close Listing') }}
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>

                        <Form
                            v-else
                            v-bind="
                                BetaReadingListingController.reopen.form({
                                    book: book.id,
                                    listing: listing.id,
                                })
                            "
                            :options="{ preserveScroll: true }"
                            class="flex flex-col items-end gap-1"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                variant="outline"
                                size="sm"
                                :disabled="processing || hasOpenListing"
                                :aria-describedby="
                                    hasOpenListing
                                        ? `reopen-listing-${listing.id}-disabled-reason`
                                        : undefined
                                "
                            >
                                <RotateCcw /> {{ t('Reopen') }}
                            </Button>
                            <p
                                v-if="hasOpenListing"
                                :id="`reopen-listing-${listing.id}-disabled-reason`"
                                class="max-w-56 text-right text-xs text-muted-foreground"
                            >
                                {{
                                    t(
                                        'Close the open Listing before reopening this one.',
                                    )
                                }}
                            </p>
                        </Form>
                    </div>
                </CardHeader>
            </Card>
        </div>

        <PaginationLinks :links="listings.links" />
    </div>
</template>
