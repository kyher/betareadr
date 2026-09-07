<script setup lang="ts">
import { Form, Link, usePage } from '@inertiajs/vue3';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import BrandLogo from '@/components/BrandLogo.vue';
import { Button } from '@/components/ui/button';

const administrator = usePage<{ administrator: { name: string } }>().props
    .administrator;
</script>

<template>
    <div class="min-h-svh bg-background">
        <header class="border-b bg-card">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <Link href="/admin" class="flex items-center gap-2">
                    <BrandLogo />
                    <span class="text-sm text-muted-foreground">Admin</span>
                </Link>
                <nav aria-label="Admin navigation">
                    <Link href="/admin" class="text-sm font-medium">
                        Dashboard
                    </Link>
                </nav>
                <div class="flex items-center gap-3">
                    <AppearanceToggle />
                    <span class="text-sm text-muted-foreground">
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
