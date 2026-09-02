<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { getInitials } from '@/composables/useInitials';
import { dashboard, home, login } from '@/routes';
import type { AuthorProfile, Book } from '@/types';

type Paginator<T> = {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{ authorProfile: AuthorProfile; books: Paginator<Book> }>();
const page = usePage();
</script>

<template>
    <Head :title="authorProfile.display_name" />
    <div class="min-h-screen bg-muted/30">
        <header class="border-b bg-background">
            <div
                class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4"
            >
                <Link :href="home()" class="text-lg font-semibold"
                    >BetaReadr</Link
                >
                <Button variant="outline" as-child>
                    <Link
                        :href="page.props.auth.user ? dashboard() : login()"
                        >{{
                            page.props.auth.user ? 'Dashboard' : 'Log in'
                        }}</Link
                    >
                </Button>
            </div>
        </header>
        <main class="mx-auto max-w-3xl px-4 py-16 sm:py-24">
            <div class="flex flex-col items-center text-center">
                <Avatar class="size-24">
                    <AvatarFallback class="text-2xl font-semibold">{{
                        getInitials(authorProfile.display_name)
                    }}</AvatarFallback>
                </Avatar>
                <h1 class="mt-6 text-3xl font-semibold tracking-tight">
                    {{ authorProfile.display_name }}
                </h1>
                <p class="mt-1 text-muted-foreground">
                    @{{ authorProfile.handle }}
                </p>
                <p
                    v-if="authorProfile.bio"
                    class="mt-8 max-w-2xl text-left leading-7 whitespace-pre-line"
                >
                    {{ authorProfile.bio }}
                </p>
            </div>
            <section class="mt-16">
                <h2 class="text-2xl font-semibold tracking-tight">Books</h2>
                <div v-if="books.data.length" class="mt-6 grid gap-4">
                    <Card v-for="book in books.data" :key="book.id">
                        <CardHeader>
                            <CardTitle>{{ book.title }}</CardTitle>
                            <CardDescription
                                v-if="book.description"
                                class="whitespace-pre-line text-foreground"
                                >{{ book.description }}</CardDescription
                            >
                        </CardHeader>
                    </Card>
                </div>
                <p v-else class="mt-4 text-muted-foreground">
                    This author has not added any books yet.
                </p>
                <PaginationLinks class="mt-6" :links="books.links" />
            </section>
        </main>
    </div>
</template>
