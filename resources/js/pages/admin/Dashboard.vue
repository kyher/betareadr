<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

defineProps<{
    counts: {
        users: number;
        authorProfiles: number;
        betaReaderProfiles: number;
        books: number;
        openListings: number;
        listings: number;
    };
}>();

const labels = {
    users: 'Users',
    authorProfiles: 'Author Profiles',
    betaReaderProfiles: 'Beta Reader Profiles',
    books: 'Books',
    openListings: 'Open Beta Reading Listings',
    listings: 'Total Beta Reading Listings',
};
</script>

<template>
    <Head title="Admin dashboard" />
    <h1 class="mb-6 text-2xl font-semibold">Dashboard</h1>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <Card v-for="(label, key) in labels" :key="key">
            <CardHeader class="pb-2">
                <CardTitle class="text-sm font-medium">{{ label }}</CardTitle>
            </CardHeader>
            <CardContent class="text-3xl font-semibold">
                {{ counts[key] }}
            </CardContent>
        </Card>
    </div>
</template>
