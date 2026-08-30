<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BookController from '@/actions/App/Http/Controllers/BookController';
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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Add Book', href: BookController.create() },
        ],
    },
});
</script>

<template>
    <Head title="Add Book" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>Add a Book</CardTitle>
                <CardDescription>
                    Add a work to your public Author Profile.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="BookController.store.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            required
                            maxlength="200"
                            autofocus
                            placeholder="The Left Hand of Darkness"
                        />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">
                            Description
                            <span class="text-muted-foreground"
                                >(optional)</span
                            >
                        </Label>
                        <textarea
                            id="description"
                            name="description"
                            maxlength="2000"
                            rows="8"
                            placeholder="Tell readers about this Book."
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.description" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link :href="books()">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            Add Book
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
