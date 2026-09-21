<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthSimpleLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { store } from '@/routes/admin/login';
</script>

<template>
    <Head title="Admin login" />

    <div class="admin-surface">
        <div class="fixed top-4 right-4 z-10">
            <AppearanceToggle />
        </div>

        <AuthSimpleLayout
            title="BetaReadr Admin"
            description="Enter your administrator credentials"
        >
            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="grid gap-6"
            >
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        autofocus
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                    />
                    <InputError :message="errors.password" />
                </div>

                <Button type="submit" :disabled="processing">
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </Form>
        </AuthSimpleLayout>
    </div>
</template>
