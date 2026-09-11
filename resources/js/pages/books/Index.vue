<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { BookOpen, List, Pencil, Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import BetaReadingListingController from '@/actions/App/Http/Controllers/BetaReadingListingController';
import BookController from '@/actions/App/Http/Controllers/BookController';
import InputError from '@/components/InputError.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
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
import { index as booksIndex } from '@/routes/books';
import type { Book } from '@/types';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{ books: Paginator<Book> }>();
const { t } = useTranslation();
const page = usePage();
const bookError = computed(
    () => (page.props.errors as Record<string, string> | undefined)?.book,
);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Books', href: booksIndex() }],
    },
});
</script>

<template>
    <Head :title="t('Books')" />
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ t('Books') }}
                </h1>
                <p class="mt-1 text-muted-foreground">
                    {{
                        t(
                            'Manage the books shown on your public author profile.',
                        )
                    }}
                </p>
            </div>
            <Button as-child>
                <Link :href="BookController.create()"
                    ><Plus /> {{ t('Add book') }}</Link
                >
            </Button>
        </div>

        <InputError v-if="bookError" :message="bookError" />

        <Card v-if="books.data.length === 0" class="border-dashed">
            <CardHeader>
                <div
                    class="mb-3 flex size-12 items-center justify-center rounded-full bg-muted"
                >
                    <BookOpen class="size-6" />
                </div>
                <CardTitle>{{ t('Add your first book') }}</CardTitle>
                <CardDescription>
                    {{
                        t(
                            'Books you add are immediately visible on your public author profile.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="BookController.create()"
                        ><Plus /> {{ t('Add book') }}</Link
                    >
                </Button>
            </CardContent>
        </Card>

        <div v-else class="grid gap-4">
            <Card v-for="book in books.data" :key="book.id" class="paper-sheet">
                <CardHeader class="gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <CardTitle>{{ book.title }}</CardTitle>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ $page.props.contentLanguages[book.language] }}
                        </p>
                        <CardDescription
                            v-if="book.description"
                            class="reading-copy mt-3 whitespace-pre-line text-foreground"
                            >{{ book.description }}</CardDescription
                        >
                        <CardDescription v-else class="mt-2 italic">
                            {{ t('No description') }}
                        </CardDescription>
                    </div>
                    <div class="flex flex-wrap gap-2 sm:shrink-0">
                        <Button variant="outline" size="sm" as-child>
                            <Link
                                :href="
                                    BetaReadingListingController.index(book.id)
                                "
                            >
                                <List /> {{ t('Listings') }}
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="BookController.edit(book.id)"
                                ><Pencil /> {{ t('Edit') }}</Link
                            >
                        </Button>
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="destructive" size="sm"
                                    ><Trash2 /> {{ t('Delete') }}</Button
                                >
                            </DialogTrigger>
                            <DialogContent>
                                <Form
                                    v-bind="
                                        BookController.destroy.form(book.id)
                                    "
                                    :options="{ preserveScroll: true }"
                                    v-slot="{ processing }"
                                >
                                    <DialogHeader>
                                        <DialogTitle
                                            >{{ t('Delete') }} “{{
                                                book.title
                                            }}”?</DialogTitle
                                        >
                                        <DialogDescription>
                                            {{
                                                t(
                                                    'This book will be permanently deleted. This cannot be undone.',
                                                )
                                            }}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-6 gap-2">
                                        <DialogClose as-child>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                >{{ t('Cancel') }}</Button
                                            >
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            {{ t('Delete book') }}
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardHeader>
            </Card>
        </div>

        <PaginationLinks :links="books.links" />
    </div>
</template>
