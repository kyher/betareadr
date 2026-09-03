<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { useTranslation } from '@/composables/useTranslation';

const page = usePage();
const { t } = useTranslation();

function change(event: Event) {
    router.post('/language', {
        locale: (event.target as HTMLSelectElement).value,
    });
}
</script>

<template>
    <label class="sr-only" for="page-language">{{ t('Language') }}</label>
    <select
        id="page-language"
        :value="page.props.locale"
        :aria-label="t('Language')"
        class="h-9 rounded-md border border-input bg-background px-2 text-sm"
        @change="change"
    >
        <option value="en">English</option>
        <option value="es">Español</option>
        <option value="fr">Français</option>
    </select>
</template>
