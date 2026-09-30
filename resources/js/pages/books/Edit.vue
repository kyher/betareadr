<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BookController from '@/actions/App/Http/Controllers/BookController';
import BookCover from '@/components/BookCover.vue';
import BookGenreField from '@/components/BookGenreField.vue';
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
import type { Book } from '@/types';

const props = defineProps<{ book: Book }>();
const { t } = useTranslation();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Books', href: books() },
            { title: 'Edit book', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${t('Edit')} ${book.title}`" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>{{ t('Edit book') }}</CardTitle>
                <CardDescription>
                    {{
                        t(
                            'Changes appear immediately on your public author profile.',
                        )
                    }}
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="BookController.update.form(props.book.id)"
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
                            :default-value="book.title"
                            autofocus
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
                            :value="book.description ?? ''"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.description" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cover_image">
                            {{ t('Cover image') }}
                            <span class="text-muted-foreground">{{
                                t('(optional)')
                            }}</span>
                        </Label>
                        <BookCover
                            v-if="book.cover_image_url"
                            :src="book.cover_image_url"
                            class="w-32"
                        />
                        <input
                            id="cover_image"
                            name="cover_image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="file:text-foreground block h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <p class="text-sm text-muted-foreground">
                            {{ t('JPEG, PNG, or WebP, up to 5 MB.') }}
                        </p>
                        <InputError :message="errors.cover_image" />
                        <label
                            v-if="book.cover_image_url"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                name="remove_cover_image"
                                value="1"
                            />
                            {{ t('Remove cover image') }}
                        </label>
                    </div>
                    <BookLanguageField
                        :language="book.language"
                        :error="errors.language"
                    />
                    <BookGenreField :genre="book.genre" :error="errors.genre" />
                    <ContentTopicsField
                        field="content_warnings"
                        :selected="book.content_warnings"
                        :errors="errors"
                    />
                    <div class="flex justify-end gap-3">
                        <Button variant="outline" as-child>
                            <Link :href="books()">{{ t('Cancel') }}</Link>
                        </Button>
                        <Button type="submit" :disabled="processing">
                            {{ t('Save changes') }}
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
