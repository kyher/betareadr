<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BetaReadingListingController from '@/actions/App/Http/Controllers/BetaReadingListingController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as books } from '@/routes/books';

type BookSummary = { id: number; title: string };
type Listing = {
    id: number;
    reading_brief: string;
    word_count: number;
    closed_at: string | null;
};

const props = defineProps<{ book: BookSummary; listing: Listing }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Listings', href: '#' },
            { title: 'Edit Listing', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Edit ${book.title} Listing`" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <div class="flex items-center gap-2">
                    <CardTitle>Edit Beta Reading Listing</CardTitle>
                    <Badge
                        :variant="listing.closed_at ? 'secondary' : 'default'"
                    >
                        {{ listing.closed_at ? 'Closed' : 'Open' }}
                    </Badge>
                </div>
                <CardDescription>{{ book.title }}</CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="
                        BetaReadingListingController.update.form({
                            book: props.book.id,
                            listing: props.listing.id,
                        })
                    "
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="word_count">Approximate word count</Label>
                        <Input
                            id="word_count"
                            name="word_count"
                            type="number"
                            min="1"
                            max="2000000"
                            step="1"
                            required
                            :default-value="listing.word_count"
                            autofocus
                        />
                        <p class="text-sm text-muted-foreground">
                            A rounded estimate is fine.
                        </p>
                        <InputError :message="errors.word_count" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="reading_brief">Reading Brief</Label>
                        <textarea
                            id="reading_brief"
                            name="reading_brief"
                            maxlength="2000"
                            rows="10"
                            required
                            :value="listing.reading_brief"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.reading_brief" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link
                                :href="
                                    BetaReadingListingController.index(book.id)
                                "
                            >
                                Cancel
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            Save Changes
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
