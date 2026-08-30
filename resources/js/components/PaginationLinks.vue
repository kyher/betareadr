<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{ url: string | null; label: string; active: boolean }>;
}>();

const paginationLabel = (label: string): string =>
    label.replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
    <nav
        v-if="links.length > 3"
        aria-label="Pagination"
        class="flex flex-wrap justify-center gap-1"
    >
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm transition-colors hover:bg-muted"
                :class="
                    link.active
                        ? 'border-primary bg-primary text-primary-foreground hover:bg-primary/90'
                        : 'bg-background'
                "
                >{{ paginationLabel(link.label) }}</Link
            >
            <span
                v-else
                class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border px-3 text-sm text-muted-foreground opacity-50"
                >{{ paginationLabel(link.label) }}</span
            >
        </template>
    </nav>
</template>
