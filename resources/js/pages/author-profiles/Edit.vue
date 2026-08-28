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
import { dashboard } from '@/routes';
import { edit as editAuthorProfile } from '@/routes/author-profile';
import type { AuthorProfile } from '@/types';

defineProps<{ authorProfile: AuthorProfile }>();
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Edit Author Profile', href: editAuthorProfile() },
        ],
    },
});
</script>

<template>
    <Head title="Edit Author Profile" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>Edit your Author Profile</CardTitle>
                <CardDescription
                    >Update the public information beta readers
                    see.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="AuthorProfileController.update.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="display_name">Display name</Label>
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
                        <Label for="handle">Author Handle</Label>
                        <Input
                            id="handle"
                            :model-value="`@${authorProfile.handle}`"
                            disabled
                        />
                        <p class="text-sm text-muted-foreground">
                            Author Handles cannot be changed.
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="bio"
                            >Bio
                            <span class="text-muted-foreground"
                                >(optional)</span
                            ></Label
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
                            ><Link :href="dashboard()">Cancel</Link></Button
                        >
                        <Button type="submit" :disabled="processing"
                            >Save changes</Button
                        >
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
