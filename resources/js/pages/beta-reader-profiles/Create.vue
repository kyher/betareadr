<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import BetaReaderProfileController from '@/actions/App/Http/Controllers/BetaReaderProfileController';
import ContentTopicsField from '@/components/ContentTopicsField.vue';
import InputError from '@/components/InputError.vue';
import ReadingLanguagesField from '@/components/ReadingLanguagesField.vue';
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
import { dashboard } from '@/routes';
import { create as createBetaReaderProfile } from '@/routes/beta-reader-profile';

const { t } = useTranslation();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            {
                title: 'Create beta reader profile',
                href: createBetaReaderProfile(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="t('Create beta reader profile')" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>{{
                    t('Create your beta reader profile')
                }}</CardTitle>
                <CardDescription>{{
                    t(
                        'Choose the public identity you will use as a beta reader.',
                    )
                }}</CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="BetaReaderProfileController.store.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="display_name">{{
                            t('Display name')
                        }}</Label>
                        <Input
                            id="display_name"
                            name="display_name"
                            required
                            minlength="2"
                            maxlength="100"
                            autocomplete="name"
                            placeholder="Keen Sci-Fi Reader"
                        />
                        <p class="text-sm text-muted-foreground">
                            {{
                                t(
                                    'Your public name. It does not need to be unique.',
                                )
                            }}
                        </p>
                        <InputError :message="errors.display_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="handle">{{
                            t('Beta reader handle')
                        }}</Label>
                        <div
                            class="flex items-center rounded-md border border-input bg-transparent focus-within:ring-2 focus-within:ring-ring/50"
                        >
                            <span class="pl-3 text-muted-foreground">@</span>
                            <Input
                                id="handle"
                                name="handle"
                                required
                                minlength="3"
                                maxlength="30"
                                pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                autocomplete="off"
                                placeholder="keen-sci-fi-reader"
                                class="border-0 shadow-none focus-visible:ring-0"
                            />
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{
                                t(
                                    'Your unique handle. Handle changes are not currently supported. Use lowercase letters, numbers, and single hyphens.',
                                )
                            }}
                        </p>
                        <InputError :message="errors.handle" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="bio"
                            >{{ t('Bio') }}
                            <span class="text-muted-foreground">{{
                                t('(optional)')
                            }}</span></Label
                        >
                        <textarea
                            id="bio"
                            name="bio"
                            maxlength="1000"
                            rows="7"
                            :placeholder="
                                t(
                                    'Tell authors a little about yourself and your reading experience.',
                                )
                            "
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.bio" />
                    </div>
                    <ReadingLanguagesField :error="errors.reading_languages" />
                    <ContentTopicsField
                        field="content_boundaries"
                        :error="errors.content_boundaries"
                    />
                    <div class="flex items-center justify-end gap-3">
                        <Button variant="outline" as-child
                            ><Link :href="dashboard()">{{
                                t('Cancel')
                            }}</Link></Button
                        >
                        <Button type="submit" :disabled="processing">{{
                            t('Create beta reader profile')
                        }}</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
