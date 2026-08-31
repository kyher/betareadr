<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BetaReadingListingController from '@/actions/App/Http/Controllers/BetaReadingListingController';
import InputError from '@/components/InputError.vue';
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
const props = defineProps<{ book: BookSummary }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Listings', href: '#' },
            { title: 'Create Listing', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`Create a Listing for ${book.title}`" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>Create a Beta Reading Listing</CardTitle>
                <CardDescription>
                    {{ book.title }} · This Listing will open immediately.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="
                        BetaReadingListingController.store.form(props.book.id)
                    "
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <InputError :message="errors.listing" />

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
                            autofocus
                            placeholder="80000"
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
                            placeholder="Describe this draft, what has changed, and the feedback you are looking for."
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <p class="text-sm text-muted-foreground">
                            Tell Beta Readers about this draft and the feedback
                            you want.
                        </p>
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
                            Create Listing
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
