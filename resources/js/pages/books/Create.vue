<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BookController from '@/actions/App/Http/Controllers/BookController';
import BookLanguageField from '@/components/BookLanguageField.vue';
import ContentTopicsField from '@/components/ContentTopicsField.vue';
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
import { useTranslation } from '@/composables/useTranslation';
import { index as books } from '@/routes/books';

const { t } = useTranslation();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Add book', href: BookController.create() },
        ],
    },
});
</script>

<template>
    <Head :title="t('Add book')" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>{{ t('Add a book') }}</CardTitle>
                <CardDescription>
                    {{ t('Add a work to your public author profile.') }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="BookController.store.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="title">{{ t('Title') }}</Label>
                        <Input
                            id="title"
                            name="title"
                            required
                            maxlength="200"
                            autofocus
                            :placeholder="t('Book title')"
                        />
                        <InputError :message="errors.title" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="description">
                            {{ t('Description') }}
                            <span class="text-muted-foreground">{{
                                t('(optional)')
                            }}</span>
                        </Label>
                        <textarea
                            id="description"
                            name="description"
                            maxlength="2000"
                            rows="8"
                            :placeholder="t('Tell readers about this book.')"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.description" />
                    </div>
                    <BookLanguageField :error="errors.language" />
                    <ContentTopicsField
                        field="content_warnings"
                        :errors="errors"
                    />
                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link :href="books()">{{ t('Cancel') }}</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ t('Add book') }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
