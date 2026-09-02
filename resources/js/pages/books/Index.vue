<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { BookOpen, List, Pencil, Plus, Trash2 } from '@lucide/vue';
import BetaReadingListingController from '@/actions/App/Http/Controllers/BetaReadingListingController';
import BookController from '@/actions/App/Http/Controllers/BookController';
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
import { index as booksIndex } from '@/routes/books';
import type { Book } from '@/types';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{ books: Paginator<Book> }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Books', href: booksIndex() }],
    },
});
</script>

<template>
    <Head title="Books" />
    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-4 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Books</h1>
                <p class="mt-1 text-muted-foreground">
                    Manage the books shown on your public author profile.
                </p>
            </div>
            <Button as-child>
                <Link :href="BookController.create()"><Plus /> Add book</Link>
            </Button>
        </div>

        <Card v-if="books.data.length === 0" class="border-dashed">
            <CardHeader>
                <div
                    class="mb-3 flex size-12 items-center justify-center rounded-full bg-muted"
                >
                    <BookOpen class="size-6" />
                </div>
                <CardTitle>Add your first book</CardTitle>
                <CardDescription>
                    Books you add are immediately visible on your public author
                    Profile.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="BookController.create()"
                        ><Plus /> Add book</Link
                    >
                </Button>
            </CardContent>
        </Card>

        <div v-else class="grid gap-4">
            <Card v-for="book in books.data" :key="book.id">
                <CardHeader class="gap-4 sm:flex-row sm:items-start">
                    <div class="min-w-0 flex-1">
                        <CardTitle>{{ book.title }}</CardTitle>
                        <CardDescription
                            v-if="book.description"
                            class="mt-3 whitespace-pre-line text-foreground"
                            >{{ book.description }}</CardDescription
                        >
                        <CardDescription v-else class="mt-2 italic">
                            No description
                        </CardDescription>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <Button variant="outline" size="sm" as-child>
                            <Link
                                :href="
                                    BetaReadingListingController.index(book.id)
                                "
                            >
                                <List /> Listings
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="BookController.edit(book.id)"
                                ><Pencil /> Edit</Link
                            >
                        </Button>
                        <Dialog>
                            <DialogTrigger as-child>
                                <Button variant="destructive" size="sm"
                                    ><Trash2 /> Delete</Button
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
                                            >Delete “{{
                                                book.title
                                            }}”?</DialogTitle
                                        >
                                        <DialogDescription>
                                            This book will be permanently
                                            deleted. This cannot be undone.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="mt-6 gap-2">
                                        <DialogClose as-child>
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                >Cancel</Button
                                            >
                                        </DialogClose>
                                        <Button
                                            type="submit"
                                            variant="destructive"
                                            :disabled="processing"
                                        >
                                            Delete book
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
