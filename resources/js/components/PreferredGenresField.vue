<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { useTranslation } from '@/composables/useTranslation';

defineProps<{
    selected?: string[];
    errors?: Record<string, string>;
}>();
const { t } = useTranslation();
</script>

<template>
    <fieldset class="grid gap-3">
        <legend class="text-sm font-medium">{{ t('Preferred genres') }}</legend>
        <p class="text-sm text-muted-foreground">
            {{
                t(
                    'Select genres to prioritise in your Feed. Leave empty to see all genres. Only you can see these preferences.',
                )
            }}
        </p>
        <label
            v-for="(name, code) in $page.props.genres"
            :key="code"
            class="flex items-center gap-3 text-sm font-normal"
        >
            <input
                type="checkbox"
                name="preferred_genres[]"
                :value="code"
                :checked="selected?.includes(code)"
            />
            {{ name }}
        </label>
        <InputError
            :message="
                errors?.preferred_genres ??
                Object.entries(errors ?? {}).find(([key]) =>
                    key.startsWith('preferred_genres.'),
                )?.[1]
            "
        />
    </fieldset>
</template>
