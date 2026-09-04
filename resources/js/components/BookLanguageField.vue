<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/composables/useTranslation';
import type { ContentLanguage } from '@/types';

defineProps<{ language?: ContentLanguage; error?: string }>();
const { t } = useTranslation();
</script>

<template>
    <div class="grid gap-2">
        <Label for="language">{{ t('Book language') }}</Label>
        <select
            id="language"
            name="language"
            required
            :value="language"
            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
        >
            <option v-if="!language" value="" disabled>
                {{ t('Choose a language') }}
            </option>
            <option
                v-for="(name, code) in $page.props.contentLanguages"
                :key="code"
                :value="code"
            >
                {{ name }}
            </option>
        </select>
        <InputError :message="error" />
    </div>
</template>
