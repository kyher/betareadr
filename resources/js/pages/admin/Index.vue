<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminReviewController from '@/actions/App/Http/Controllers/AdminReviewController';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { show as showAuthor } from '@/routes/authors';
import { show as showBetaReader } from '@/routes/beta-readers';

type Profile = { display_name: string; handle: string };
type Record = {
    id: number;
    name?: string;
    email?: string;
    author?: Profile | null;
    reader?: Profile | null;
    handle?: string;
    user?: string;
    title?: string;
    language?: string;
    genre?: string | null;
    warnings?: string[];
    description?: string | null;
    closed_at?: string | null;
    word_count?: number;
    reading_brief?: string;
    created_at: string;
};

defineProps<{
    type:
        | 'users'
        | 'author-profiles'
        | 'beta-reader-profiles'
        | 'books'
        | 'listings';
    search: string;
    status?: 'all' | 'open' | 'closed';
    records: {
        data: Record[];
        links: { url: string | null; label: string; active: boolean }[];
    };
}>();

const titles = {
    users: 'Users',
    'author-profiles': 'Author Profiles',
    'beta-reader-profiles': 'Beta Reader Profiles',
    books: 'Books',
    listings: 'Beta Reading Listings',
};
const topics = usePage<{ contentTopics: { [key: string]: string } }>().props
    .contentTopics;
const date = (value: string) => new Date(value).toLocaleDateString('en-GB');
const destinations = {
    users: AdminReviewController.users(),
    'author-profiles': AdminReviewController.authorProfiles(),
    'beta-reader-profiles': AdminReviewController.betaReaderProfiles(),
    books: AdminReviewController.books(),
    listings: AdminReviewController.listings(),
};
const removeUser = (record: Record) => {
    if (
        confirm(
            `Remove ${record.name}? Their profiles, books, listings, active and ended matches, and entire conversations—including other participants' messages—will be permanently deleted. This cannot be undone.`,
        )
    ) {
        router.delete(AdminReviewController.destroyUser(record.id).url, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="titles[type]" />
    <h1 class="mb-6 text-2xl font-semibold">{{ titles[type] }}</h1>
    <form method="get" class="mb-6 flex flex-wrap items-end gap-3">
        <label class="grid gap-1 text-sm font-medium">
            Search
            <input
                name="q"
                :value="search"
                maxlength="200"
                class="h-9 rounded-md border bg-background px-3"
            />
        </label>
        <label
            v-if="type === 'listings'"
            class="grid gap-1 text-sm font-medium"
        >
            Status
            <select
                name="status"
                :value="status"
                class="h-9 rounded-md border bg-background px-3"
            >
                <option value="all">All</option>
                <option value="open">Open</option>
                <option value="closed">Closed</option>
            </select>
        </label>
        <button
            type="submit"
            class="h-9 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground"
        >
            Apply
        </button>
        <Link
            v-if="search || (status && status !== 'all')"
            :href="destinations[type]"
            class="py-2 text-sm underline"
            >Clear</Link
        >
    </form>

    <p v-if="!records.data.length" class="text-muted-foreground">
        No records found.
    </p>
    <div v-else class="space-y-3">
        <article
            v-for="record in records.data"
            :key="record.id"
            class="rounded-lg border bg-card p-4"
        >
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="font-semibold">
                    <template v-if="type === 'users'">{{
                        record.name
                    }}</template>
                    <Link
                        v-else-if="type === 'author-profiles' && record.handle"
                        :href="showAuthor(record.handle)"
                        class="underline"
                        >{{ record.name }}</Link
                    >
                    <Link
                        v-else-if="
                            type === 'beta-reader-profiles' && record.handle
                        "
                        :href="showBetaReader(record.handle)"
                        class="underline"
                        >{{ record.name }}</Link
                    >
                    <template v-else>{{ record.title }}</template>
                </h2>
                <span class="text-sm text-muted-foreground">{{
                    date(record.created_at)
                }}</span>
            </div>

            <template v-if="type === 'users'">
                <p class="text-sm">{{ record.email }}</p>
                <p v-if="record.author" class="text-sm">
                    Author:
                    <Link
                        :href="showAuthor(record.author.handle)"
                        class="underline"
                        >{{ record.author.display_name }}</Link
                    >
                </p>
                <p v-if="record.reader" class="text-sm">
                    Beta Reader:
                    <Link
                        :href="showBetaReader(record.reader.handle)"
                        class="underline"
                        >{{ record.reader.display_name }}</Link
                    >
                </p>
                <Button
                    class="mt-3"
                    variant="destructive"
                    size="sm"
                    @click="removeUser(record)"
                >
                    Remove user
                </Button>
            </template>
            <template
                v-else-if="
                    type === 'author-profiles' ||
                    type === 'beta-reader-profiles'
                "
            >
                <p class="text-sm text-muted-foreground">
                    @{{ record.handle }} · User: {{ record.user }}
                </p>
            </template>
            <template v-else-if="type === 'books'">
                <p class="text-sm">
                    Author:
                    <Link
                        v-if="record.author"
                        :href="showAuthor(record.author.handle)"
                        class="underline"
                        >{{ record.author.display_name }}</Link
                    >
                    · {{ record.language
                    }}<template v-if="record.genre">
                        · {{ record.genre }}</template
                    >
                </p>
                <p class="text-sm">
                    Content Warnings:
                    {{
                        record.warnings?.length
                            ? record.warnings
                                  .map((warning) => topics[warning] ?? warning)
                                  .join(', ')
                            : 'No content warnings declared'
                    }}
                </p>
                <p
                    v-if="record.description"
                    class="mt-3 text-sm whitespace-pre-wrap"
                >
                    {{ record.description }}
                </p>
            </template>
            <template v-else>
                <p class="text-sm">
                    #{{ record.id }} ·
                    {{ record.closed_at ? 'Closed' : 'Open' }} ·
                    {{ record.word_count?.toLocaleString() }} words
                </p>
                <p class="text-sm">
                    Author:
                    <Link
                        v-if="record.author"
                        :href="showAuthor(record.author.handle)"
                        class="underline"
                        >{{ record.author.display_name }}</Link
                    >
                </p>
                <p class="mt-3 text-sm whitespace-pre-wrap">
                    {{ record.reading_brief }}
                </p>
            </template>
        </article>
    </div>
    <PaginationLinks class="mt-6" :links="records.links" />
</template>
