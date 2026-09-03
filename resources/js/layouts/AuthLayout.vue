<script setup lang="ts">
import LanguageSelect from '@/components/LanguageSelect.vue';
import { useTranslation } from '@/composables/useTranslation';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
const { t } = useTranslation();
</script>

<template>
    <div class="fixed top-4 right-4 z-10"><LanguageSelect /></div>
    <AuthLayout :title="t(title)" :description="t(description)">
        <slot />
    </AuthLayout>
</template>
