<script setup lang="ts">
import { Form, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

const administrator = usePage<{ administrator: { name: string } }>().props
    .administrator;
</script>

<template>
    <div class="min-h-svh bg-muted/30">
        <header class="border-b bg-background">
            <div
                class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4"
            >
                <Link href="/admin" class="font-semibold">BetaReadr Admin</Link>
                <div class="flex items-center gap-3">
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
