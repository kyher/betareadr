<script setup lang="ts">
import { Moon, Sun } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { useTranslation } from '@/composables/useTranslation';

const { resolvedAppearance, updateAppearance } = useAppearance();
const { t } = useTranslation();
const dark = computed(() => resolvedAppearance.value === 'dark');
const label = computed(() =>
    t(dark.value ? 'Switch to light mode' : 'Switch to dark mode'),
);
</script>

<template>
    <Button
        type="button"
        variant="ghost"
        size="icon"
        :aria-label="label"
        :title="label"
        @click="updateAppearance(dark ? 'light' : 'dark')"
    >
        <Sun v-if="dark" aria-hidden="true" />
        <Moon v-else aria-hidden="true" />
    </Button>
</template>
