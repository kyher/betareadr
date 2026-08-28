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
import { create as createAuthorProfile } from '@/routes/author-profile';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
            { title: 'Create Author Profile', href: createAuthorProfile() },
        ],
    },
});
</script>

<template>
    <Head title="Create Author Profile" />
    <div class="mx-auto w-full max-w-2xl p-4 md:p-8">
        <Card>
            <CardHeader>
                <CardTitle>Create your Author Profile</CardTitle>
                <CardDescription
                    >Choose the public identity you will use as an
                    Author.</CardDescription
                >
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="AuthorProfileController.store.form()"
                    class="space-y-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="grid gap-2">
                        <Label for="display_name">Display name</Label>
                        <Input
                            id="display_name"
                            name="display_name"
                            required
                            minlength="2"
                            maxlength="100"
                            autocomplete="name"
                            placeholder="Ursula Le Guin"
                        />
                        <p class="text-sm text-muted-foreground">
                            Your public name or pen name. It does not need to be
                            unique.
                        </p>
                        <InputError :message="errors.display_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="handle">Author Handle</Label>
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
                                placeholder="ursula-le-guin"
                                class="border-0 shadow-none focus-visible:ring-0"
                            />
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Your unique handle. Handle changes are not currently
                            supported. Use lowercase letters, numbers, and
                            single hyphens.
                        </p>
                        <InputError :message="errors.handle" />
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
                            placeholder="Tell beta readers a little about yourself and your writing."
                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
                        />
                        <InputError :message="errors.bio" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <Button variant="outline" as-child
                            ><Link :href="dashboard()">Cancel</Link></Button
                        >
                        <Button type="submit" :disabled="processing"
                            >Create Author Profile</Button
                        >
                    </div>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>
