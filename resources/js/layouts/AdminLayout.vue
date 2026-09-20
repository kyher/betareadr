<script setup lang="ts">
import { Form, Link, usePage } from '@inertiajs/vue3';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import BrandLogo from '@/components/BrandLogo.vue';
import { Button } from '@/components/ui/button';

const administrator = usePage<{ administrator: { name: string } }>().props
    .administrator;
</script>

<template>
    <div class="admin-surface min-h-svh bg-background">
        <header class="border-b bg-card">
            <div
                class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-3 py-3 sm:px-6 sm:py-4"
            >
                <Link
                    href="/admin"
                    class="flex shrink-0 items-center gap-1 sm:gap-2"
                >
                    <BrandLogo class="text-lg [&_svg]:size-8" />
                    <span class="hidden text-sm text-muted-foreground sm:inline"
                        >Admin</span
                    >
                </Link>
                <nav
                    aria-label="Admin navigation"
                    class="order-3 flex w-full flex-wrap gap-3 text-sm font-medium md:order-none md:w-auto"
                >
                    <Link href="/admin">Dashboard</Link>
                    <Link href="/admin/users">Users</Link>
                    <Link href="/admin/author-profiles">Authors</Link>
                    <Link href="/admin/beta-reader-profiles">Beta Readers</Link>
                    <Link href="/admin/books">Books</Link>
                    <Link href="/admin/listings">Listings</Link>
                </nav>
                <div class="flex items-center gap-3">
                    <AppearanceToggle />
                    <span
                        class="hidden text-sm text-muted-foreground md:inline"
                    >
                        {{ administrator.name }}
                    </span>
                    <Form action="/admin/logout" method="post">
                        <Button variant="ghost" type="submit">Log out</Button>
                    </Form>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-6xl p-6">
            <slot />
        </main>
    </div>
</template>
