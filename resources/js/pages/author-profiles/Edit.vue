<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AuthorProfileController from '@/actions/App/Http/Controllers/AuthorProfileController';
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
import { dashboard } from '@/routes';
import { edit as editAuthorProfile } from '@/routes/author-profile';
import type { AuthorProfile } from '@/types';

defineProps<{ authorProfile: AuthorProfile }>();
const { t } = useTranslation();
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Edit author profile', href: editAuthorProfile() },
        ],
    },
});
</script>

<template>
    <Head :title="t('Edit author profile')" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>{{ t('Edit your author profile') }}</CardTitle>
                <CardDescription>{{
                    t('Update the public information beta readers see.')
                }}</CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="AuthorProfileController.update.form()"
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
                            :default-value="authorProfile.display_name"
                            required
                            minlength="2"
                            maxlength="100"
                            autocomplete="name"
                        />
                        <InputError :message="errors.display_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="handle">{{ t('Author handle') }}</Label>
                        <Input
                            id="handle"
                            :model-value="`@${authorProfile.handle}`"
                            disabled
                        />
                        <p class="text-sm text-muted-foreground">
                            {{ t('Author handles cannot be changed.') }}
                        </p>
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
                            :value="authorProfile.bio ?? ''"
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.bio" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <Button variant="outline" as-child
                            ><Link :href="dashboard()">{{
                                t('Cancel')
                            }}</Link></Button
                        >
                        <Button type="submit" :disabled="processing">{{
                            t('Save changes')
                        }}</Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
