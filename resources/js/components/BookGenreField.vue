<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/composables/useTranslation';
import type { Genre } from '@/types';

defineProps<{ genre?: Genre | null; error?: string }>();
const { t } = useTranslation();
</script>

<template>
    <div class="grid gap-2">
        <Label for="genre">{{ t('Genre') }}</Label>
        <select
            id="genre"
            name="genre"
            required
            :value="genre"
            class="h-9 rounded-md border border-input bg-transparent px-3 text-sm shadow-xs"
        >
            <option v-if="!genre" value="" disabled>
                {{ t('Choose a genre') }}
            </option>
            <option
                v-for="(name, code) in $page.props.genres"
                :key="code"
                :value="code"
            >
                {{ name }}
            </option>
        </select>
        <InputError :message="error" />
    </div>
</template>
