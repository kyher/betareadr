<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppearanceToggle from '@/components/AppearanceToggle.vue';
import BrandLogo from '@/components/BrandLogo.vue';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/composables/useTranslation';
import { dashboard, home, login } from '@/routes';

const page = usePage();
const { t } = useTranslation();
</script>

<template>
    <header class="border-b bg-transparent">
        <div
            class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4"
        >
            <Link :href="home()"><BrandLogo /></Link>
            <div class="flex items-center gap-2">
                <AppearanceToggle />
                <Button variant="outline" as-child>
                    <Link :href="page.props.auth.user ? dashboard() : login()">
                        {{ t(page.props.auth.user ? 'Dashboard' : 'Log in') }}
                    </Link>
                </Button>
            </div>
        </div>
    </header>
</template>
